{{-- «Настройки кабинетов» — лагерь «Кабинеты участниц»: тема кабинета, AI (поиск, подбор контактов,
     AI-помощник в кабинете) и база знаний помощника. Настройки публичного сайта — в «Внешний сайт → Настройки сайта». --}}
<x-layouts.admin :title="__('Настройки кабинетов')">
    <div class="form-column" x-data="{ tab: @js($tab) }">

        <div class="tabs mb-6 overflow-x-auto no-scrollbar">
            <button type="button" class="tab whitespace-nowrap" :class="{ 'tab--active': tab === 'theme' }"
                    @click="tab = 'theme'">{{ __('Тема кабинета') }}</button>
            <button type="button" class="tab whitespace-nowrap" :class="{ 'tab--active': tab === 'ai' }"
                    @click="tab = 'ai'">{{ __('AI-провайдеры') }}</button>
            <button type="button" class="tab whitespace-nowrap" :class="{ 'tab--active': tab === 'knowledge' }"
                    @click="tab = 'knowledge'">{{ __('База знаний ассистента') }}</button>
            <button type="button" class="tab whitespace-nowrap" :class="{ 'tab--active': tab === 'manager' }"
                    @click="tab = 'manager'">{{ __('AI-менеджер') }}</button>
        </div>

        <div x-show="tab === 'theme'" @if($tab !== 'theme') style="display:none" @endif>
            @include('admin.cabinets.settings._theme')
        </div>
        <div x-show="tab === 'ai'" @if($tab !== 'ai') style="display:none" @endif>
            @include('admin.cabinets.settings._ai')
        </div>
        <div x-show="tab === 'knowledge'" @if($tab !== 'knowledge') style="display:none" @endif>
            @include('admin.cabinets.settings._knowledge')
        </div>
        <div x-show="tab === 'manager'" @if($tab !== 'manager') style="display:none" @endif>
            @include('admin.cabinets.settings._manager')
        </div>
    </div>
</x-layouts.admin>
