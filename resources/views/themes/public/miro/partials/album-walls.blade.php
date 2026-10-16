{{-- Альбомы на странице «Фото»: название со ссылкой, число фото и все фото рядами («полотно»). Отдельный файл, потому что
     те же альбомы приходят и при подгрузке по кнопке «Смотреть ещё» (AlbumsController, ответ с ?partial=1).
     Просмотр листает фото одного альбома (data-lightbox-group) и открывает их в полном размере. --}}
@php $locales = $locales ?? ['ru', 'en', 'ro']; @endphp
@foreach($albums as $album)
    <section class="miro-wall" id="album-{{ $album['slug'] }}" data-lightbox-group>
        <h2 class="miro-wall__title"><a href="{{ $album['href'] }}">@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $album['title'][$locale] }}</span>@endforeach</a></h2>
        @if(! empty($album['count']))
            <p class="miro-wall__count">@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $album['count'][$locale] }}</span>@endforeach</p>
        @endif
        @include('themes.public.miro.partials.blocks', ['blocks' => $album['blocks'], 'lang' => 'ru', 'fullPhotos' => true])
    </section>
@endforeach
