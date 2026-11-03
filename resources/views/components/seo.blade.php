{{-- Метаданные публичной страницы для поисковиков и превью в соцсетях (на русском): заголовок вкладки, описание,
     канонический адрес, Open Graph и карточка Twitter. Вызывается из <head> каждой страницы сайта:
     <x-seo title="…" description="…" :image="…" /> (image — адрес картинки материала; без неё общая картинка сайта).
     noindex закрывает страницу от индексации (черновик, видимый только администратору). --}}
@props(['title', 'description' => '', 'image' => null, 'type' => 'website', 'noindex' => false])
@php
    $siteName = "Women's Hub";
    $text = trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $description), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    $text = \Illuminate\Support\Str::limit($text, 160, '…');
    $cover = $image ?: asset_v('themes/public/miro/images/bannerhero.png');
    $cover = preg_match('#^https?://#i', $cover) ? $cover : url($cover);
    $canonical = url()->current();
@endphp
<title>{{ $title }}</title>
@if($text !== '')
<meta name="description" content="{{ $text }}">
@endif
<link rel="canonical" href="{{ $canonical }}">
@if($noindex)
<meta name="robots" content="noindex,nofollow">
@endif
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="ru_RU">
<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $title }}">
@if($text !== '')
<meta property="og:description" content="{{ $text }}">
@endif
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $cover }}">
<meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $title }}">
@if($text !== '')
<meta name="twitter:description" content="{{ $text }}">
@endif
<meta name="twitter:image" content="{{ $cover }}">
