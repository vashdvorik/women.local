{{-- Блоки материала (текст, заголовок, HTML-код, файл PDF, картинка, галереи). Структура общая для
     всех языков, поэтому список блоков выводится отдельно для каждого языка в своей обёртке data-lang.
     $blocks — массив блоков одного языка, $lang — этот язык (для подписей вроде кнопки «Скачать»). --}}
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

            @case('file')
                {{-- «Файл (PDF)»: каталог или брошюра. Если файла нет на диске, блок не выводится. --}}
                @php $file = \App\Support\FileBlock::info($data, $lang ?? 'ru'); @endphp
                @if($file)
                    <div class="miro-prose__file">
                        <span class="miro-prose__file-icon" aria-hidden="true">PDF</span>
                        <div class="miro-prose__file-body">
                            <div class="miro-prose__file-title">{{ $file['title'] }}</div>
                            @if($file['size'])
                                <div class="miro-prose__file-meta">PDF · {{ $file['size'] }}</div>
                            @endif
                        </div>
                        <a class="miro-prose__file-button" href="{{ $file['url'] }}" download="{{ $file['download'] }}"
                           aria-label="{{ $file['button'] }}: {{ $file['title'] }}">{{ $file['button'] }}</a>
                    </div>
                @endif
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
