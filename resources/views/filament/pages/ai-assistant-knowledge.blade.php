<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        <x-filament::section heading="Как работает база знаний">
            <div class="space-y-2 text-sm leading-6 text-gray-600">
                <p>Помощник получает этот текст вместе со свежими данными каталога участниц и возможностей при каждом вопросе. История чата не сохраняется.</p>
                <p>Контакты участниц — Telegram, телефоны, email, адреса и ссылки для связи — в AI не отправляются. Для статичных страниц сайта (например, «О нас») поддерживайте информацию здесь вручную.</p>
            </div>
        </x-filament::section>

        <x-filament::section heading="Общие правила для помощника">
            <x-filament::input.wrapper>
                <textarea wire:model="rules" rows="7" class="fi-input block w-full border-0 bg-transparent py-2 text-sm shadow-none outline-none ring-0" placeholder="Например: всегда отвечай доброжелательно, не придумывай факты, предложи следующий полезный шаг."></textarea>
            </x-filament::input.wrapper>
        </x-filament::section>

        @foreach(['ru' => 'Русский', 'en' => 'English', 'ro' => 'Română'] as $locale => $label)
            <x-filament::section :heading="'Информация о платформе — ' . $label">
                <x-filament::input.wrapper>
                    <textarea wire:model="{{ $locale }}" rows="12" class="fi-input block w-full border-0 bg-transparent py-2 text-sm shadow-none outline-none ring-0" placeholder="Добавьте проверенную информацию о платформе, разделах сайта, команде, правилах и программах."></textarea>
                </x-filament::input.wrapper>
            </x-filament::section>
        @endforeach

        <x-filament::button type="submit">Сохранить базу знаний</x-filament::button>
    </form>
</x-filament-panels::page>
