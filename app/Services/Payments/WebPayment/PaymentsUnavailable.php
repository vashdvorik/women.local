<?php

declare(strict_types=1);

namespace App\Services\Payments\WebPayment;

use RuntimeException;

/** Принимать платежи сейчас нельзя (не настроен банк, имитатор на боевом сервере и т. п.). Текст сообщения безопасен для показа участнице. */
class PaymentsUnavailable extends RuntimeException {}
