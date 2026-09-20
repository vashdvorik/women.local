{{-- Блоки материала (текст, заголовок, HTML-код, картинка, галереи). Структура общая для всех
     языков, поэтому список блоков выводится отдельно для каждого языка в своей обёртке data-lang.
     $blocks — массив блоков одного языка. --}}
<div class="miro-prose">
    @foreach($blocks as $block)
        @php $data = $block['data'] ?? []; @endphp

        @switch($block['type'] ?? '')
            @case('text')
                {{-- HTML блока «текст» очищается на выводе по белому списку (config/purifier.php). --}}
                <div class="miro-prose__text">{!! clean($data['html'] ?? '', 'content_block') !!}</div>
                @break

            @case('embed')
                {{-- Блок «HTML-код» выводится как есть: осознанное исключение, редактор один и доверенный. --}}
                <div class="miro-prose__embed">{!! $data['html'] ?? '' !!}</div>
                @break

            @case('heading')
                @if(($data['level'] ?? 'h2') === 'h3')
                    <h3>{{ $data['text'] ?? '' }}</h3>
                @else
                    <h2>{{ $data['text'] ?? '' }}</h2>
                @endif
                @break

            @case('image')
                @if($data['path'] ?? null)
                    <img class="miro-prose__image" src="/uploads/{{ $data['path'] }}" alt="" loading="lazy">
                @endif
                @break

            @case('gallery_2')
            @case('gallery_3')
            @case('gallery_4')
                @php
                    $imgs = array_values(array_filter($data['images'] ?? []));
                    $portrait = $block['type'] === 'gallery_4';
                    $cols = match ($block['type']) {
                        'gallery_3' => 3,
                        'gallery_4' => 4,
                        default => 2,
                    };
                @endphp
                @if($imgs)
                    <div class="miro-prose__gallery miro-prose__gallery--{{ $cols }} @if($portrait) is-portrait @endif">
                        @foreach($imgs as $img)
                            <img src="/uploads/{{ $img }}" alt="" loading="lazy">
                        @endforeach
                    </div>
                @endif
                @break
        @endswitch
    @endforeach
</div>
