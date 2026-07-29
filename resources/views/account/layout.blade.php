@php
    $accountTheme = $accountTheme ?? \App\Models\SiteSetting::accountTheme();
    $accountTheme = array_key_exists($accountTheme, \App\Models\SiteSetting::ACCOUNT_THEMES) ? $accountTheme : 'classic';
@endphp

@include('themes.account.' . $accountTheme . '.layout', ['accountTheme' => $accountTheme])
