<?php

declare(strict_types=1);

namespace App\Services\Payments\WebPayment;

use RuntimeException;

/** Банк недоступен или ответил так, что ответу нельзя доверять (сеть, формат, подпись). Платёж при этом не теряется: его перепроверит reconcile. */
class GatewayException extends RuntimeException {}
