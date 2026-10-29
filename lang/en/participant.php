<?php

// Registration, email sign-in, the profile form and plan requests. The Russian original is in lang/ru.
return [

    'or' => 'or',
    'back_to_site' => '← Back to the site',

    'google' => [
        'login' => 'Sign in with Google',
        'register' => 'Continue with Google',
        'failed' => 'We could not sign you in with Google. Try again or sign in with your email.',
        'no_email' => 'Google did not provide a verified email address, so this sign-in method cannot be used.',
    ],

    'login' => [
        'title' => 'Sign in',
        'heading' => 'Sign in to your account',
        'subtitle' => 'Member account',
        'email' => 'Email',
        'password' => 'Password',
        'remember' => 'Remember me',
        'submit' => 'Sign in',
        'forgot' => 'Forgot your password?',
        'no_account' => 'No account yet?',
        'register_link' => 'Create one',
        'failed' => 'Wrong email or password.',
        'throttle' => 'Too many sign-in attempts. Try again in :seconds s.',
        'access_closed' => 'Access to your account is closed. If this is a mistake, write to us through the Contacts page.',
        'session_expired' => 'Your session has ended. Please sign in again.',
    ],

    'register' => [
        'title' => 'Sign up',
        'heading' => 'Create your account',
        'subtitle' => 'Email and password. You will fill in your profile and choose a plan in the next steps.',
        'email' => 'Email',
        'password' => 'Password',
        'password_hint' => 'At least 8 characters',
        'password_confirmation' => 'Repeat the password',
        'consent' => 'I agree to the processing of my personal data and accept the',
        'consent_link' => 'platform rules',
        'submit' => 'Sign up',
        'have_account' => 'Already have an account?',
        'login_link' => 'Sign in',
    ],

    'verification' => [
        'title' => 'Email confirmation',
        'heading' => 'Confirm your email',
        'text' => 'We sent a message to :email. Open it and follow the link: after that you can fill in your profile.',
        'spam' => 'No message? Check your spam folder.',
        'resend' => 'Send the message again',
        'resent' => 'The message has been sent again.',
        'verified' => 'Email confirmed. Now tell us about yourself.',
        'invalid' => 'The link is invalid or has expired. Request a new message.',
        'wrong_email' => 'Used the wrong email?',
        'logout' => 'Sign out and start over',
        'sent' => 'We have sent a message with a confirmation link.',
    ],

    'password' => [
        'forgot_title' => 'Password recovery',
        'forgot_heading' => 'Forgot your password?',
        'forgot_text' => 'Enter the email your account is registered with and we will send you a link to set a new password.',
        'forgot_submit' => 'Send the link',
        'forgot_sent' => 'If this email is registered, we have sent a message with a link to it.',
        'reset_title' => 'New password',
        'reset_heading' => 'Set a new password',
        'reset_submit' => 'Save the password',
        'reset_done' => 'Your password has been changed. Sign in with the new password.',
        'reset_invalid' => 'The password link is invalid or has expired. Request a new one.',
        'back_to_login' => 'Back to sign in',
    ],

    'onboarding' => [
        'title' => 'Profile',
        'step_email' => 'Email',
        'step_profile' => 'Profile',
        'step_plan' => 'Plan',
        'heading' => 'Tell us about yourself',
        'subtitle' => 'An administrator will review your profile, after which other members of the platform will see you. We need your phone number to contact you.',
        'full_name' => 'Full name',
        'phone' => 'Phone',
        'phone_hint' => 'For example, +373 600 00 000',
        'description' => 'About you and your work',
        'description_hint' => 'What you do, your experience or your business.',
        'expectation' => 'What you expect from the platform',
        'expectation_hint' => 'Which contacts, opportunities and knowledge you need.',
        'submit' => 'Continue to plans',
    ],

    'validation' => [
        'email_required' => 'Enter your email.',
        'email_invalid' => 'Check the email address.',
        'email_taken' => 'This email is already registered. Sign in or recover your password.',
        'password_required' => 'Choose a password.',
        'password_min' => 'The password must be at least 8 characters long.',
        'password_confirmed' => 'The passwords do not match.',
        'consent' => 'You need to agree to the data processing and the platform rules.',
        'full_name_required' => 'Enter your full name.',
        'phone_required' => 'Enter your phone number.',
        'phone_invalid' => 'Check the phone number: digits, optionally with “+”, spaces and brackets (8 to 15 digits).',
        'description_required' => 'Tell us a little about yourself.',
        'expectation_required' => 'Write what you expect from the platform.',
    ],

    'plans' => [
        'choose_heading' => 'Choose a plan',
        'choose_text' => 'There is no free access: a plan opens your account. Your profile is saved, one last step remains.',
        'buy' => 'Buy',
        'request' => 'Leave a request',
        'request_hint' => 'This plan is currently issued by a manager. Leave a request and they will call you and open the purchase.',
        'comment' => 'Comment for the manager (optional)',
        'request_sent' => 'Request sent',
        'request_sent_text' => 'A manager will call you at :phone and open the purchase.',
        'unlocked' => 'A manager has opened the purchase of this plan for you.',
        'requested_flash' => 'Your request has been received. A manager will call you at :phone.',
        'not_closed' => 'This plan can be bought right away, no request is needed.',
        'phone_missing' => 'Fill in your phone number in the profile: the manager will call it.',
    ],

    'telegram' => [
        'title' => 'Telegram for notifications',
        'text' => 'Connect the bot to receive messages about payments and the end of your plan, matching contacts and platform newsletters. You cannot sign in through Telegram: sign-in is by email only.',
        'connect' => 'Connect Telegram',
        'connected' => 'Telegram connected',
        'disconnect' => 'Disconnect',
        'disconnected' => 'Telegram disconnected, you will not receive notifications.',
    ],

    'banner' => [
        'pending' => 'Your profile is under review: other members will see you once an administrator approves it.',
        'rejected' => 'Your profile was declined. If this is a mistake, write to us through the Contacts page.',
    ],

    'contacts' => [
        'phone' => 'Phone',
        'locked' => 'Members’ contacts are open from the Active plan.',
    ],

    'mail' => [
        'greeting' => 'Hello!',
        'footer' => 'This message was sent automatically, there is no need to reply.',
        'verify' => [
            'subject' => 'Confirm your email — Women’s hub',
            'line' => 'You are signing up for a Women’s hub account. Press the button to confirm your email and go to your profile.',
            'button' => 'Confirm email',
            'note' => 'The link is valid for 24 hours. If you did not sign up, just ignore this message.',
        ],
        'reset' => [
            'subject' => 'New password — Women’s hub',
            'line' => 'You asked to change the password of your Women’s hub account. Press the button to set a new one.',
            'button' => 'Set a new password',
            'note' => 'The link is valid for 60 minutes. If you did not ask for this, your password stays the same and you can delete this message.',
        ],
        'subscription' => [
            'activated' => ['subject' => 'The :plan plan is active — Women’s hub', 'line' => 'The :plan plan is active until :until. Thank you for being with us!'],
            'reminder' => ['subject' => 'The :plan plan ends soon — Women’s hub', 'line' => 'The :plan plan is valid until :until, :days days left. Renew it on the Subscription page so you do not lose access.'],
            'expired' => ['subject' => 'The :plan plan has ended — Women’s hub', 'line' => 'The :plan plan has ended. Choose a plan on the Subscription page to get your account back.'],
            'note' => 'Connect Telegram in your profile to receive such messages there too.',
        ],
        'moderation' => [
            'approved' => ['subject' => 'Profile approved — Women’s hub', 'line' => 'Your profile has been approved: other members of the platform can now see you.'],
            'rejected' => ['subject' => 'Decision on your profile — Women’s hub', 'line' => 'Thank you for your interest in Women’s hub. At the moment your profile has not been approved. To clarify the details, write to us through the Contacts page of the website.'],
            'revoked' => ['subject' => 'Access closed — Women’s hub', 'line' => 'Access to Women’s hub has been closed. If you have questions about participation, write to us through the Contacts page of the website.'],
            'button' => 'Open my account',
            'note' => 'Connect Telegram in your profile to receive such messages there too.',
        ],
        'opened' => [
            'subject' => 'Purchase of the :plan plan is open for you — Women’s hub',
            'line' => 'A manager has opened the purchase of the :plan plan for you. The plan now has a “Buy” button on the Subscription page of your account.',
            'button' => 'Go to plans',
            'note' => 'Payment is by bank card, and the plan is activated as soon as the bank confirms the payment.',
        ],
    ],
];
