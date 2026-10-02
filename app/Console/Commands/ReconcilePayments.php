<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Payments\WebPayment\PaymentReconciler;
use Illuminate\Console\Command;

/**
 * Фоновая сверка неоплаченных счетов с банком (GetState). Нужна на случай, если оповещение банка (ResultURL) не
 * дошло: документ банка прямо говорит, что при сбое ResultURL повторов не будет, и платёж «считается завершённым
 * успешно». Команда находит такие счета, дозапрашивает у банка их состояние, включает тариф за оплаченные и закрывает
 * просроченные. Расписание — каждые 5 минут (routes/console.php).
 */
class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile';

    protected $description = 'Сверить неоплаченные счета Web-платежа с банком (GetState)';

    public function handle(PaymentReconciler $reconciler): int
    {
        $count = $reconciler->reconcileOpen();

        $this->info("Проверено счетов: {$count}.");

        return self::SUCCESS;
    }
}
