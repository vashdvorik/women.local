<?php

declare(strict_types=1);

namespace App\Services\Payments\WebPayment;

use App\Models\Payment;

/**
 * Канал связи с банком. Два исполнителя: BankGateway (настоящий Агропромбанк) и FakeGateway (имитатор для
 * разработки и тестов). Выбирается настройкой webpayment.driver.
 */
interface Gateway
{
    /** 'bank' или 'fake': записывается в платёж, чтобы по журналу было видно, настоящий он или нет. */
    public function driver(): string;

    /** Куда браузер отправляет форму со счётом (раздел 3 документа). */
    public function startUrl(Payment $payment): string;

    /**
     * Текущее состояние счёта в банке (GetState).
     *
     * @throws GatewayException если банк недоступен или ответ не удалось проверить
     */
    public function getState(Payment $payment): BankState;
}
