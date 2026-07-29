@php
    $landingTheme = $landingTheme ?? \App\Models\SiteSetting::landingTheme();
@endphp

@include('themes.public.' . $landingTheme . '.landing', [
    'landingTheme' => $landingTheme,
    'publicTheme' => $landingTheme,
])
