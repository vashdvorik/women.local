<?php

return [
    'home' => 'Home',
    'cabinet' => 'To my account',
    'login' => 'Sign in',
    'back' => 'Back',
    'reload' => 'Reload the page',
    'contact' => 'If the error keeps happening, write to us',
    'wait' => 'Wait :seconds s and try again.',

    'session_expired' => 'The page was open for too long and your session ended. Nothing was lost: please repeat the action.',
    'too_many' => 'Too many attempts. Wait :seconds s and try again.',

    '403' => ['title' => 'Access denied', 'text' => 'You do not have access to this page. If you think this is a mistake, sign in or write to us.'],
    '404' => ['title' => 'Page not found', 'text' => 'The address may have a typo, or the page has moved. Start from the home page or open your account.'],
    '419' => ['title' => 'Your session has ended', 'text' => 'The page was open for too long. Reload it and repeat the action: nothing bad happened.'],
    '429' => ['title' => 'Too many attempts', 'text' => 'Please wait a little and try again. A minute is usually enough.'],
    '500' => ['title' => 'Something went wrong', 'text' => 'This is on our side and we are already looking into it. Reload the page in a minute.'],
    '503' => ['title' => 'Maintenance in progress', 'text' => 'Everything will be back soon. Please check again in a few minutes.'],
    '4xx' => ['title' => 'The page could not be opened', 'text' => 'The request could not be completed. Go back and try again.'],
];
