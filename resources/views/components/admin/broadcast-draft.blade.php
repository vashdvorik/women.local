@props(['item'])

{{-- Галочка «Создать черновик для рассылки» в формах новости, публикации и возможности. Сам материал она не меняет и
     ничего не отправляет: создаёт черновик в разделе «Рассылка», где админ правит текст и отправляет (сразу или по
     расписанию). На один материал черновик создаётся один раз. --}}
@php
    $existing = $item->exists ? app(\App\Services\Broadcasts\BroadcastDrafts::class)->existing($item) : collect();
@endphp

<section class="card space-y-3">
    <h3 class="text-section font-semibold">{{ __('Рассылка участницам') }}</h3>

    @if($existing->isNotEmpty())
        <p class="text-ui text-ink-muted">{{ __('Рассылка по этому материалу уже создана:') }}</p>
        <ul class="space-y-1">
            @foreach($existing as $broadcast)
                <li class="flex flex-wrap items-center gap-2 text-ui">
                    <span class="badge {{ $broadcast->statusBadge() }}">{{ $broadcast->statusLabel() }}</span>
                    <a href="{{ route('admin.broadcasts.show', $broadcast) }}" class="table-link" target="_blank" rel="noopener">{{ __('Открыть в разделе «Рассылка»') }}</a>
                </li>
            @endforeach
        </ul>
    @else
        <label class="flex items-start gap-2 text-ui">
            <input type="checkbox" name="broadcast_draft" value="1" class="mt-1" @checked(old('broadcast_draft'))>
            <span>
                <span class="font-medium">{{ __('Создать черновик для рассылки') }}</span>
                <span class="block text-ink-muted">{{ __('После сохранения в разделе «Рассылка» появится черновик: заголовок и описание на трёх языках, обложка и кнопка «Подробнее» со ссылкой на страницу. Ничего не отправляется: поправьте текст там и отправьте сразу или в назначенное время. Отправляйте после публикации, пока материал не опубликован, ссылка не откроется.') }}</span>
            </span>
        </label>
        <div class="space-y-1">
            <label for="broadcast_audience" class="field-label">{{ __('Кому предложить отправить') }}</label>
            <select id="broadcast_audience" name="broadcast_audience" class="field-input !w-auto">
                @foreach(\App\Models\Broadcast::AUDIENCES as $audience)
                    <option value="{{ $audience }}" @selected(old('broadcast_audience', \App\Models\Broadcast::AUDIENCE_APPROVED) === $audience)>{{ \App\Models\Broadcast::audienceTitle($audience) }}</option>
                @endforeach
            </select>
        </div>
    @endif
</section>
