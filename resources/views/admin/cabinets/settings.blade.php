{{-- «Настройки кабинетов» — лагерь «Кабинеты участниц»: тема кабинета, ИИ (поиск, подбор контактов,
     AI-помощник в кабинете) и база знаний помощника. Настройки публичного сайта — в «Внешний сайт → Настройки сайта». --}}
<x-layouts.admin title="Настройки кабинетов">
    <div class="form-column" x-data="{ tab: @js($tab) }">

        <div class="tabs mb-6 overflow-x-auto no-scrollbar">
            <button type="button" class="tab whitespace-nowrap" :class="{ 'tab--active': tab === 'theme' }"
                    @click="tab = 'theme'">Тема кабинета</button>
            <button type="button" class="tab whitespace-nowrap" :class="{ 'tab--active': tab === 'ai' }"
                    @click="tab = 'ai'">ИИ-провайдеры</button>
            <button type="button" class="tab whitespace-nowrap" :class="{ 'tab--active': tab === 'knowledge' }"
                    @click="tab = 'knowledge'">База знаний ассистента</button>
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
    </div>
</x-layouts.admin>
