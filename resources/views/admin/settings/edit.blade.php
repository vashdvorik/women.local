{{-- «Настройки сайта» — лагерь «Внешний сайт». Настройки кабинета участниц — в «Кабинеты участниц → Настройки кабинетов». --}}
<x-layouts.admin title="Настройки сайта">
    <div class="form-column" x-data="{ tab: @js($tab) }">

        <div class="tabs mb-6 overflow-x-auto no-scrollbar">
            <button type="button" class="tab whitespace-nowrap" :class="{ 'tab--active': tab === 'images' }"
                    @click="tab = 'images'">Сжатие изображений</button>
            <button type="button" class="tab whitespace-nowrap" :class="{ 'tab--active': tab === 'theme' }"
                    @click="tab = 'theme'">Тема сайта</button>
        </div>

        <div x-show="tab === 'images'" @if($tab !== 'images') style="display:none" @endif>
            @include('admin.settings._images')
        </div>
        <div x-show="tab === 'theme'" @if($tab !== 'theme') style="display:none" @endif>
            @include('admin.settings._theme')
        </div>
    </div>
</x-layouts.admin>
