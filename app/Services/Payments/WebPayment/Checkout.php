<?php

declare(strict_types=1);

namespace App\Services\Payments\WebPayment;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\Payment;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Создание счёта на подписку и данные формы для платёжного сайта банка (раздел 3 документа).
 *
 * Деньги проходят так: участница нажимает «Оплатить» → мы создаём Payment (pending) и отдаём ей страницу с формой,
 * которая сама отправляется на платёжный сайт банка → после оплаты банк вызывает наш ResultURL (ResultHandler) →
 * мы проверяем подпись и подтверждаем оплату запросом GetState (PaymentReconciler) → включается тариф.
 */
final class Checkout
{
    /** Сколько раз пробуем взять другой номер счёта, если выпавший уже занят. */
    private const INVOICE_ATTEMPTS = 5;

    public function __construct(private readonly Gateway $gateway) {}

    /**
     * @throws PaymentsUnavailable если принимать платежи сейчас нельзя или смена тарифа невозможна
     */
    public function begin(BotUser $user, Plan $plan): Payment
    {
        if (! $plan->isPaid()) {
            throw new InvalidArgumentException('Оплачиваются только Community и Private.');
        }

        $this->assertAvailable();

        // Уже действует тариф выше: платить за меньший нельзя, он входит в текущий.
        if ($user->currentPlan()->rank() > $plan->rank()) {
            throw new PaymentsUnavailable(__('subscription.errors.already_higher', ['plan' => $user->currentPlan()->title()]));
        }

        // Не заводим новый счёт на каждое нажатие: пока предыдущий не истёк, показываем его же. Но только если цена
        // с тех пор не менялась: после смены цены в админке участница получает счёт на новую сумму.
        $existing = Payment::query()
            ->where('bot_user_id', $user->id)
            ->where('plan', $plan->value)
            ->where('amount', $plan->priceKopecks())
            ->where('driver', $this->gateway->driver())
            ->where('is_test', (bool) config('webpayment.is_test'))
            ->where('status', Payment::STATUS_PENDING)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $isTest = (bool) config('webpayment.is_test');

        // Номер случайный, поэтому совпадение с уже занятым возможно (хоть и крайне маловероятно): уникальный индекс
        // базы его не пропустит, и мы просто берём другой.
        for ($attempt = 1; ; $attempt++) {
            try {
                return Payment::create([
                    'bot_user_id' => $user->id,
                    'telegram_id' => $user->telegram_id,
                    'plan' => $plan->value,
                    'months' => $plan->months(),
                    'amount' => $plan->priceKopecks(),
                    'currency' => (string) config('webpayment.currency_code'),
                    'invoice_id' => $this->invoiceId($isTest),
                    'status' => Payment::STATUS_PENDING,
                    'is_test' => $isTest,
                    'driver' => $this->gateway->driver(),
                    'expires_at' => now()->addMinutes((int) config('webpayment.lifetime', 30)),
                ]);
            } catch (UniqueConstraintViolationException $e) {
                if ($attempt >= self::INVOICE_ATTEMPTS) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Поля формы для платёжного сайта банка. Названия и регистр — как в документе (раздел 3).
     *
     * @return array<string, string>
     */
    public function fields(Payment $payment): array
    {
        $login = (string) config('webpayment.merchant_login');
        $description = $this->description($payment);

        return [
            'MerchantLogin' => $login,
            'RequestSum' => (string) $payment->amount,
            'RequestCurrCode' => $payment->currency,
            'nivid' => $payment->invoice_id,
            'Desc' => $description,
            'IsTest' => $payment->is_test ? '1' : '0',
            'LifeTime' => (string) config('webpayment.lifetime', 30),
            'SignatureValue' => Signature::request(
                $login,
                $payment->invoice_id,
                $payment->is_test,
                $payment->amount,
                $payment->currency,
                $description,
                (string) config('webpayment.merchant_pass'),
            ),
        ];
    }

    public function startUrl(Payment $payment): string
    {
        return $this->gateway->startUrl($payment);
    }

    /**
     * Описание счёта. Нарочно только латиницей и цифрами: оно входит в строку подписи, а документ не уточняет
     * кодировку этой строки (UTF-8 или 1251). ASCII одинаков в обеих, поэтому подпись не зависит от неё.
     */
    public function description(Payment $payment): string
    {
        // Telegram ID плательщицы виден в выписке банка: если база потеряется (авария, откат к старой копии), по выписке
        // можно будет понять, кому из участниц вернуть тариф. Двоеточия нет: оно разделяет поля строки подписи.
        return 'Womens Hub '.$payment->planEnum()->shortTitle().' membership '.$payment->invoice_id.' tg'.$payment->telegram_id;
    }

    /**
     * Номер счёта (nivid): до 20 символов, например WH261002K4M9QZ, а для тестового — WHT261002K4M9QZ
     * (приставка, [T], дата ггммдд и 6 случайных символов).
     *
     * Банк требует, чтобы номер был уникален за всё время работы торговца. Поэтому он не строится из номера строки
     * в базе: после восстановления старой копии или пересоздания базы счётчик строк откатился бы назад и выдал
     * номера, которые банк уже видел. Дата и случайная часть от базы не зависят.
     */
    public function invoiceId(bool $isTest): string
    {
        return (string) config('subscription.invoice_prefix', 'WH').($isTest ? 'T' : '').now()->format('ymd').Str::upper(Str::random(6));
    }

    /** @throws PaymentsUnavailable */
    private function assertAvailable(): void
    {
        if ($this->gateway instanceof FakeGateway && app()->isProduction()) {
            throw new PaymentsUnavailable(__('subscription.errors.unavailable'));
        }

        if ($this->gateway instanceof BankGateway
            && (blank(config('webpayment.merchant_login')) || blank(config('webpayment.merchant_pass')))) {
            // Подробность — в журнал, а не участнице.
            logger()->error('Web-платёж не настроен: не заданы WEBPAYMENT_MERCHANT_LOGIN / WEBPAYMENT_MERCHANT_PASS.');

            throw new PaymentsUnavailable(__('subscription.errors.unavailable'));
        }
    }
}
