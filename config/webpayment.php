<?php

// Имитатор банка и его учётные данные существуют только вне боевого сервера: на APP_ENV=production значений
// по умолчанию нет вообще, и без настоящих WEBPAYMENT_MERCHANT_LOGIN / WEBPAYMENT_MERCHANT_PASS платёж не создаётся.
$production = env('APP_ENV', 'production') === 'production';
$fake = ! $production && env('WEBPAYMENT_DRIVER', 'fake') === 'fake';

return [

    /*
    |--------------------------------------------------------------------------
    | Web-платёж ЗАО «Агропромбанк»
    |--------------------------------------------------------------------------
    |
    | Описание протокола: docs/WebPayment-Документация.md (оригинал — docs/WebPayment-Документация.pdf).
    | Как это подключено у нас: docs/SUBSCRIPTIONS.md.
    |
    | driver:
    |   bank — настоящий платёжный сайт и веб-сервис банка;
    |   fake — «имитатор банка» для разработки и тестов (страница оплаты на нашем же сайте). На боевом
    |          сервере (APP_ENV=production) не работает: платёж там создать нельзя.
    |
    | is_test — признак IsTest в запросе к банку. По умолчанию true: пока вы не выставили WEBPAYMENT_TEST=false,
    | деньги не списываются. Для боевых платежей это нужно сделать сознательно.
    |
    */

    'driver' => env('WEBPAYMENT_DRIVER', $production ? 'bank' : 'fake'),

    'is_test' => (bool) env('WEBPAYMENT_TEST', true),

    // Выдаются банком при заключении соглашения. Только в .env, в репозиторий не попадают.
    // Для имитатора (локально и в тестах) подставляются условные значения, на боевом сервере — никогда.
    'merchant_login' => env('WEBPAYMENT_MERCHANT_LOGIN', $fake ? '000123' : null),
    'merchant_pass' => env('WEBPAYMENT_MERCHANT_PASS', $fake ? 'local-fake-merchant-pass' : null),

    // Платёжный сайт банка, куда браузер отправляет данные счёта (п. 3 документа).
    'start_url' => env('WEBPAYMENT_START_URL', 'https://epay.apb.online/PaymentStart'),

    // Веб-сервис для административных функций (п. 6): оттуда мы проверяем состояние счёта (GetState).
    'service_url' => env('WEBPAYMENT_SERVICE_URL', 'https://ws.agroprombank.com/merchant/APB.SV.WebPayment.AgentService.asmx'),
    'service_namespace' => 'http://webpayment.services.agroprombank.com/',

    // Код валюты счёта: «Рубль ПМР - 000» (п. 3 документа).
    'currency_code' => '000',

    // Время жизни счёта в минутах (документ: 30 — полчаса, 90 — полтора часа; согласуется с банком при регистрации).
    'lifetime' => (int) env('WEBPAYMENT_LIFETIME', 30),

    // Сколько секунд ждать ответа банка при проверке счёта.
    'timeout' => (int) env('WEBPAYMENT_TIMEOUT', 15),

    /*
    | Проверка электронной подписи банка в ответах веб-сервиса (п. 6 документа). Документ не называет алгоритм,
    | поэтому он настраивается: укажите файл сертификата банка (PEM) и алгоритм, который подтвердит банк.
    | Пока сертификат не задан, подлинность ответа обеспечивает защищённое соединение (HTTPS) с узлом банка.
    */
    'bank_cert' => env('WEBPAYMENT_BANK_CERT'),
    'bank_signature_algo' => env('WEBPAYMENT_BANK_SIGNATURE_ALGO', 'sha256'),

];
