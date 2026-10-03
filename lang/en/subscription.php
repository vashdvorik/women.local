<?php

return [

    'currency' => 'rub.',
    'free' => 'Free',
    'per_year' => 'per year',

    'nav' => [
        'subscription' => 'Subscription',
        'private' => 'Private',
    ],

    // Plan descriptions: translation of the customer's Russian texts (draft; to be proofread).
    'plans' => [
        'open' => [
            'title' => 'WOMEN’S HUB OPEN',
            'tagline' => 'Free access',
            'lead' => 'Open access to the information resources of WOMEN’S HUB:',
            'features' => [
                'useful materials for women;',
                'information about business, career and personal development;',
                'announcements of open events;',
                'information about educational programmes;',
                'stories of successful women;',
                'information about grants, competitions and projects;',
                'the option to register for open events.',
            ],
        ],
        'community' => [
            'title' => 'WOMEN’S HUB COMMUNITY',
            'tagline' => 'Full membership',
            'lead' => 'Full membership in the WOMEN’S HUB women’s community:',
            'features' => [
                'participation in the Gala “Woman of the Year”;',
                'access to the closed community;',
                'participation in WOMEN’S HUB open events;',
                'special prices for events and programmes;',
                'access to lectures and educational meetings;',
                'information about grants, competitions and projects;',
                'discounts and offers from partners;',
                'the opportunity to present your business or project;',
                'networking and new business contacts;',
                'participation in WOMEN’S HUB initiatives.',
            ],
        ],
        'private' => [
            'title' => 'WOMEN’S HUB PRIVATE',
            'tagline' => 'Premium membership',
            'lead' => 'Premium membership and personal support from WOMEN’S HUB:',
            'features' => [
                'all the benefits of the Community package;',
                'participation in the closed Private Circle;',
                'a personal curator throughout the year;',
                'a personal strategy session;',
                'legal and financial-economic consultations;',
                'individual selection of useful contacts;',
                'closed business meetings and private dinners;',
                'priority participation in projects and delegations;',
                'personal promotion of your business or project;',
                'the opportunity to initiate a joint project with WOMEN’S HUB.',
            ],
        ],
    ],

    'page' => [
        'title' => 'Subscription',
        'eyebrow' => 'Plans',
        'heading' => 'Your access to WOMEN’S HUB',
        'intro' => 'Three access levels. Open is free; Community and Private are paid for a year by bank card.',
        'current' => 'Your plan',
        'until' => 'valid until :date',
        'no_expiry' => 'no expiry',
        'your_plan' => 'Your plan',
        'included' => 'Included in your plan',
        'subscribe' => 'Subscribe for :price',
        'renew' => 'Renew for :price',
        'upgrade' => 'Upgrade to :plan for :price',
        'pay_note' => 'Payment by bank card on the secure Agroprombank page. We never receive your card details.',
        'test_mode' => 'Test mode: no money is charged.',
        'renew_hint' => 'Renewing adds 12 months to your current term.',
        'lower_included' => 'Included in your plan',
        'open_badge' => 'Current',
    ],

    'history' => [
        'title' => 'Payments',
        'empty' => 'No payments yet.',
        'date' => 'Date',
        'plan' => 'Plan',
        'amount' => 'Amount',
        'status' => 'Status',
        'status_labels' => [
            'pending' => 'Awaiting payment',
            'verifying' => 'Being verified by the bank',
            'paid' => 'Paid',
            'failed' => 'Not paid',
            'cancelled' => 'Cancelled',
            'expired' => 'Expired',
        ],
    ],

    'private' => [
        'title' => 'Private',
        'eyebrow' => 'Premium membership',
        'heading' => 'WOMEN’S HUB PRIVATE',
        'active' => 'Your Private membership is valid until :date.',
        'active_text' => 'Your personal curator will contact you. If you need to reach the team, write to the project manager on Telegram.',
        'intro' => 'Premium membership for those who want more: a personal curator, a closed circle and individual support throughout the year.',
        'contact' => 'Write to the manager',
    ],

    'locked' => [
        'notice' => 'This section is part of the :plan plan and above. Subscribe to unlock it.',
        'json' => 'This feature is available with a subscription.',
        'title' => 'This section is for subscribers',
        'text' => 'The members directory, contact matching, search, the AI assistant and publishing opportunities are part of WOMEN’S HUB COMMUNITY.',
        'cta' => 'See the plans',
    ],

    'open_home' => [
        'title' => 'Home',
        'eyebrow' => 'WOMEN’S HUB OPEN',
        'hello' => 'Hello, :name!',
        'intro' => 'You have open access: materials, announcements and information from the platform website. The members, contact search and the AI assistant unlock with a subscription.',
        'upgrade_title' => 'Open the community',
        'upgrade_text' => 'WOMEN’S HUB COMMUNITY: the members directory, networking, the closed community, partner discounts and participation in the Gala.',
        'upgrade_cta' => 'Get Community',
        'news' => 'News and announcements',
        'publications' => 'Publications',
        'opportunities' => 'Opportunities: grants, competitions, projects',
        'all' => 'All on the website',
        'empty' => 'Nothing here yet.',
        'profile' => 'My profile',
    ],

    'result' => [
        'title' => 'Payment',
        'paid_title' => 'Payment received',
        'paid_text' => 'Your :plan subscription is active until :date. Thank you!',
        'processing_title' => 'Checking your payment',
        'processing_text' => 'The bank has not confirmed the payment yet. It usually takes less than a minute and this page refreshes by itself. If you have already paid, the subscription will be activated automatically; there is no need to pay again.',
        'failed_title' => 'Payment failed',
        'failed_text' => 'The payment was not completed and no money was charged. You can try again.',
        'unknown_title' => 'Payment not found',
        'unknown_text' => 'We could not find this payment. If money was charged, the subscription will be activated automatically; check the “Subscription” section in a few minutes.',
        'back' => 'Back to subscription',
        'cabinet' => 'To my account',
        'redirecting' => 'Taking you to the bank’s secure payment page…',
        'redirect_button' => 'Go to payment',
    ],

    'errors' => [
        'unavailable' => 'Payment is currently unavailable. Please try again later or write to the project manager.',
        'already_higher' => 'You already have :plan, which includes the selected plan.',
        'invalid_plan' => 'Please choose a plan.',
        'checkout_throttled' => 'Too many attempts. Please wait a minute.',
    ],

];
