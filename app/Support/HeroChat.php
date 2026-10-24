<?php

namespace App\Support;

/**
 * Переписка с Telegram-ботом на первом экране главной темы miro2 (resources/views/themes/public/miro2/landing.blade.php,
 * анимация — public/themes/public/miro2/js/hero-chat.js): участница задаёт вопрос, ИИ-помощник отвечает и показывает
 * подходящую участницу (аватар у обоих сообщений помощника — «AI», как в кабинете). Сценарии по кругу, имена и профили вымышленные, чтобы не показывать реальных людей без их согласия.
 * Тексты на трёх языках, правятся здесь.
 */
final class HeroChat
{
    /**
     * @return list<array{
     *     question: array<string, string>,
     *     answer: array<string, string>,
     *     person: array{name: array<string, string>, role: array<string, string>, note: array<string, string>, match: int}
     * }>
     */
    public static function scenes(): array
    {
        return [
            [
                'question' => self::t('Ищу партнёра, чтобы запустить онлайн-магазин', 'Looking for a partner to launch an online shop', 'Caut un partener pentru un magazin online'),
                'answer' => self::t('Нашлось совпадение с участницей из вашего региона:', 'I found a match in your region:', 'Am găsit o potrivire din regiunea ta:'),
                'person' => [
                    'name' => self::t('Анна К.', 'Anna K.', 'Ana C.'),
                    'role' => self::t('Студия керамики · Тирасполь', 'Ceramics studio · Tiraspol', 'Studio de ceramică · Tiraspol'),
                    'note' => self::t('Ищет партнёра по онлайн-продажам', 'Looking for an online sales partner', 'Caută un partener de vânzări online'),
                    'match' => 94,
                ],
            ],
            [
                'question' => self::t('Нужен наставник по выходу на экспорт', 'I need a mentor for going export', 'Am nevoie de un mentor pentru export'),
                'answer' => self::t('Вот эксперты по теме. Лучший вариант:', 'I selected experts on the topic. The best fit:', 'Am ales experți pe temă. Cea mai bună potrivire:'),
                'person' => [
                    'name' => self::t('Мария Д.', 'Maria D.', 'Maria D.'),
                    'role' => self::t('Консультант по ВЭД · Кишинёв', 'Foreign trade consultant · Chisinau', 'Consultant comerț exterior · Chișinău'),
                    'note' => self::t('Менторство: экспорт в страны ЕС', 'Mentoring: exports to the EU', 'Mentorat: export în UE'),
                    'match' => 91,
                ],
            ],
            [
                'question' => self::t('Хочу найти поставщика эко-упаковки', 'I want to find an eco-packaging supplier', 'Vreau un furnizor de ambalaje ecologice'),
                'answer' => self::t('Нашёл 3 поставщика упаковочных материалов. Ближе всего от Вас:', 'I found 3 suppliers of packaging materials. Closest to you:', 'Am găsit 3 furnizori de ambalaje. Cel mai aproape de tine:'),
                'person' => [
                    'name' => self::t('Елена С.', 'Elena S.', 'Elena S.'),
                    'role' => self::t('Производство упаковки · Бендеры', 'Packaging production · Bender', 'Producție ambalaje · Bender'),
                    'note' => self::t('Эко-упаковка для малого бизнеса', 'Eco packaging for small business', 'Ambalaje ecologice pentru afaceri mici'),
                    'match' => 89,
                ],
            ],
        ];
    }

    /** @return array<string, string> */
    private static function t(string $ru, string $en, string $ro): array
    {
        return ['ru' => $ru, 'en' => $en, 'ro' => $ro];
    }
}
