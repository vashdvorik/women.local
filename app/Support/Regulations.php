<?php

namespace App\Support;

/**
 * Текст «Положения о платформе Woman’s Hub» (страница /about/regulations) на трёх языках.
 *
 * Русский текст — дословно из документа docs/pologenie/Womans_Hub_Polozhenie_EBA_19000.docx; английский и
 * румынский — переводы. Названия организаций даны так же, как на странице партнёров. Перевод юридического текста стоит
 * показать юристу, прежде чем считать его окончательным.
 *
 * Структура: разделы (id для якоря, заголовок), в каждом блоки четырёх видов:
 *  - clause: пункт с номером («1.1.») и текстом;
 *  - lead: вводная фраза перед списком;
 *  - list: маркированный список;
 *  - package: пакет участия (plan — тариф, цена берётся из настроек сайта, номер, название и нумерованный список).
 *
 * Цены пакетов в тексте не хранятся: страница берёт их у App\Enums\Plan (админка «Подписки → Цена подписки»), чтобы
 * положение и оплата никогда не расходились.
 */
final class Regulations
{
    /**
     * @return array{
     *     title: array<string, string>, subtitle: array<string, string>, intro: array<string, string>,
     *     contents: array<string, string>, price: array<string, string>, includes: array<string, string>,
     *     sections: list<array{id: string, title: array<string, string>, blocks: list<array<string, mixed>>}>
     * }
     */
    public static function content(): array
    {
        return [
            'title' => self::t('Положение', 'Regulations', 'Regulament'),
            'subtitle' => self::t(
                'ПЛАТФОРМА ДЛЯ ЖЕНЩИН-ПРЕДПРИНИМАТЕЛЕЙ WOMAN’S HUB',
                'PLATFORM FOR WOMEN ENTREPRENEURS WOMAN’S HUB',
                'PLATFORMĂ PENTRU FEMEI ANTREPRENOARE WOMAN’S HUB',
            ),
            // Описание страницы для поисковиков и заголовка вкладки.
            'intro' => self::t(
                'Положение о платформе Woman’s Hub: цели, партнёрство, членство, пакеты участия, права и обязанности резидентов.',
                'Regulations of the Woman’s Hub platform: goals, partnership, membership, participation packages, rights and obligations of residents.',
                'Regulamentul platformei Woman’s Hub: scopuri, parteneriat, calitatea de membră, pachete de participare, drepturile și obligațiile rezidentelor.',
            ),
            'contents' => self::t('Содержание', 'Contents', 'Cuprins'),
            'price' => self::t('Стоимость: :price рублей / год.', 'Price: :price rubles / year.', 'Cost: :price ruble / an.'),
            'free' => self::t('Бесплатно', 'Free', 'Gratuit'),
            'includes' => self::t('В пакет входит:', 'The package includes:', 'Pachetul include:'),
            'sections' => [
                [
                    'id' => 'general',
                    'title' => self::t('1. Общие положения', '1. General provisions', '1. Dispoziții generale'),
                    'blocks' => [
                        self::clause('1.1.',
                            'Woman’s Hub является платформой для женщин-предпринимателей и специализированной площадкой для объединения женщин, заинтересованных в предпринимательстве, профессиональном развитии, деловом взаимодействии и реализации совместных инициатив.',
                            'Woman’s Hub is a platform for women entrepreneurs and a specialized venue that brings together women interested in entrepreneurship, professional development, business cooperation and joint initiatives.',
                            'Woman’s Hub este o platformă pentru femeile antreprenoare și un spațiu specializat care reunește femeile interesate de antreprenoriat, dezvoltare profesională, colaborare în afaceri și realizarea unor inițiative comune.'),
                        self::clause('1.2.',
                            'Woman’s Hub создаётся и развивается на базе Агентства Инноваций и Развития, которое является институциональной и организационной основой проекта.',
                            'Woman’s Hub is created and developed on the basis of the Agency for Innovation and Development, which is the institutional and organizational foundation of the project.',
                            'Woman’s Hub este creată și dezvoltată în cadrul Agenției pentru Inovare și Dezvoltare, care constituie baza instituțională și organizatorică a proiectului.'),
                        self::clause('1.3.',
                            'Woman’s Hub объединяет женщин-предпринимателей, руководителей, специалистов, экспертов, представителей креативных и социальных направлений, а также других активных участниц деловой и общественной жизни.',
                            'Woman’s Hub unites women entrepreneurs, executives, specialists, experts, representatives of creative and social fields, as well as other active participants in business and public life.',
                            'Woman’s Hub reunește femei antreprenoare, manageri, specialiste, experte, reprezentante ale domeniilor creative și sociale, precum și alte participante active la viața de afaceri și cea publică.'),
                        self::clause('1.4.',
                            'Деятельность Woman’s Hub основывается на принципах взаимной поддержки, профессиональной этики, уважения, сотрудничества и развития предпринимательской инициативы.',
                            'The activities of Woman’s Hub are based on the principles of mutual support, professional ethics, respect, cooperation and the development of entrepreneurial initiative.',
                            'Activitatea Woman’s Hub se bazează pe principiile sprijinului reciproc, eticii profesionale, respectului, cooperării și dezvoltării inițiativei antreprenoriale.'),
                    ],
                ],
                [
                    'id' => 'goals',
                    'title' => self::t('2. Цели и задачи Woman’s Hub', '2. Goals and objectives of Woman’s Hub', '2. Scopurile și obiectivele Woman’s Hub'),
                    'blocks' => [
                        self::lead('Основными целями Woman’s Hub являются:', 'The main goals of Woman’s Hub are:', 'Principalele scopuri ale Woman’s Hub sunt:'),
                        self::bullets([
                            ['развитие женского предпринимательства и профессионального потенциала;', 'developing women’s entrepreneurship and professional potential;', 'dezvoltarea antreprenoriatului feminin și a potențialului profesional;'],
                            ['объединение активных женщин в устойчивую платформу;', 'uniting active women in a sustainable platform;', 'reunirea femeilor active într-o platformă stabilă;'],
                            ['создание условий для деловых контактов, партнёрства и совместных проектов;', 'creating conditions for business contacts, partnership and joint projects;', 'crearea condițiilor pentru contacte de afaceri, parteneriat și proiecte comune;'],
                            ['предоставление информационных, образовательных, экспертных и консультационных возможностей;', 'providing informational, educational, expert and consulting opportunities;', 'oferirea de oportunități informaționale, educaționale, de expertiză și de consultanță;'],
                            ['содействие продвижению бизнеса, проектов и профессиональной экспертизы участниц;', 'supporting the promotion of members’ businesses, projects and professional expertise;', 'sprijinirea promovării afacerilor, proiectelor și expertizei profesionale ale participantelor;'],
                            ['вовлечение женщин в предпринимательские, социальные, креативные и общественные инициативы.', 'engaging women in entrepreneurial, social, creative and public initiatives.', 'implicarea femeilor în inițiative antreprenoriale, sociale, creative și civice.'],
                        ]),
                    ],
                ],
                [
                    'id' => 'partnership',
                    'title' => self::t('3. Организационная основа и партнёрство', '3. Organizational basis and partnership', '3. Baza organizatorică și parteneriatul'),
                    'blocks' => [
                        self::clause('3.1.',
                            'Woman’s Hub действует на базе Агентства Инноваций и Развития.',
                            'Woman’s Hub operates on the basis of the Agency for Innovation and Development.',
                            'Woman’s Hub își desfășoară activitatea în cadrul Agenției pentru Inovare și Dezvoltare.'),
                        self::clause('3.2.',
                            'Для реализации целей и программ Woman’s Hub взаимодействует с партнёрскими организациями, экспертами, предпринимателями, образовательными и общественными структурами.',
                            'To implement the goals and programs of Woman’s Hub, it cooperates with partner organizations, experts, entrepreneurs, and educational and public bodies.',
                            'Pentru realizarea scopurilor și programelor sale, Woman’s Hub colaborează cu organizații partenere, experți, antreprenori, structuri educaționale și obștești.'),
                        self::clause('3.3.',
                            'Партнёрами Woman’s Hub являются Ассоциация социальных предпринимателей и Ассоциация креативных индустрий. В качестве дополнительных партнёров могут привлекаться иные организации, в том числе EBA и AFAM, в соответствии с отдельными соглашениями.',
                            'The partners of Woman’s Hub are the Association of Social Entrepreneurs and the Association of Creative Industries. Other organizations, including EBA and AFAM, may be involved as additional partners under separate agreements.',
                            'Partenerii Woman’s Hub sunt Asociația Antreprenorilor Sociali și Asociația Industriilor Creative. În calitate de parteneri suplimentari pot fi atrase și alte organizații, inclusiv EBA și AFAM, în baza unor acorduri separate.'),
                        self::clause('3.4.',
                            'Партнёрское взаимодействие может включать образовательные программы, консультационную и экспертную поддержку, развитие предпринимательских инициатив, networking, мероприятия и совместные проекты.',
                            'Partnership cooperation may include educational programs, consulting and expert support, development of entrepreneurial initiatives, networking, events and joint projects.',
                            'Colaborarea cu partenerii poate include programe educaționale, sprijin consultativ și de expertiză, dezvoltarea inițiativelor antreprenoriale, networking, evenimente și proiecte comune.'),
                    ],
                ],
                [
                    'id' => 'membership',
                    'title' => self::t('4. Членство и статус резидента', '4. Membership and resident status', '4. Calitatea de membră și statutul de rezidentă'),
                    'blocks' => [
                        self::clause('4.1.',
                            'Участницей Woman’s Hub может стать женщина, разделяющая цели и ценности платформы.',
                            'A woman who shares the goals and values of the platform may become a member of Woman’s Hub.',
                            'Poate deveni participantă a Woman’s Hub orice femeie care împărtășește scopurile și valorile platformei.'),
                        self::clause('4.2.',
                            'Членство является индивидуальным и осуществляется на условиях выбранного пакета: Woman’s Hub Community или Woman’s Hub Private.',
                            'Membership is individual and is provided under the terms of the chosen package: Woman’s Hub Community or Woman’s Hub Private.',
                            'Calitatea de membră este individuală și se acordă în condițiile pachetului ales: Woman’s Hub Community sau Woman’s Hub Private.'),
                        self::clause('4.3.',
                            'Для вступления необходимо подать заявку и оплатить соответствующий членский взнос.',
                            'To join, a woman must submit an application and pay the corresponding membership fee.',
                            'Pentru aderare este necesar să se depună o cerere și să se achite cotizația de membru corespunzătoare.'),
                        self::clause('4.4.',
                            'Статус резидента действует в течение оплаченного периода членства.',
                            'Resident status is valid for the paid membership period.',
                            'Statutul de rezidentă este valabil pe durata perioadei de membru achitate.'),
                        self::clause('4.5.',
                            'Порядок вступления, продления и прекращения членства определяется настоящим Положением и отдельными регламентами.',
                            'The procedure for joining, renewing and terminating membership is determined by these Regulations and by separate rules.',
                            'Procedura de aderare, prelungire și încetare a calității de membră se stabilește prin prezentul Regulament și prin regulamente separate.'),
                    ],
                ],
                [
                    'id' => 'committee',
                    'title' => self::t('5. Комиссия по отбору участниц Woman’s Hub', '5. Woman’s Hub Member Selection Committee', '5. Comisia de selecție a participantelor Woman’s Hub'),
                    'blocks' => [
                        self::clause('5.1.',
                            'В целях соблюдения общих критериев вступления и формирования состава резидентов Woman’s Hub действует Комиссия по отбору участниц.',
                            'In order to comply with the general admission criteria and to form the body of residents of Woman’s Hub, the Woman’s Hub Member Selection Committee operates.',
                            'Pentru respectarea criteriilor generale de aderare și formarea componenței rezidentelor Woman’s Hub, funcționează Comisia de selecție a participantelor.'),
                        self::clause('5.2.',
                            'Комиссия состоит из шести человек: трёх представителей Агентства Инноваций и Развития и трёх представителей партнёрских организаций Woman’s Hub.',
                            'The Committee consists of six people: three representatives of the Agency for Innovation and Development and three representatives of Woman’s Hub partner organizations.',
                            'Comisia este formată din șase persoane: trei reprezentanți ai Agenției pentru Inovare și Dezvoltare și trei reprezentanți ai organizațiilor partenere ale Woman’s Hub.'),
                        self::clause('5.3.',
                            'Представители партнёрских организаций в составе Комиссии определяются из числа действующих партнёров Woman’s Hub.',
                            'The representatives of partner organizations on the Committee are chosen from among the current partners of Woman’s Hub.',
                            'Reprezentanții organizațiilor partenere în componența Comisiei sunt desemnați dintre partenerii activi ai Woman’s Hub.'),
                        self::clause('5.4.',
                            'Комиссия рассматривает заявки на участие и принимает решение о возможности вступления женщины в Woman’s Hub в соответствии с целями и ценностями платформы.',
                            'The Committee reviews applications for participation and decides whether a woman may join Woman’s Hub in accordance with the goals and values of the platform.',
                            'Comisia examinează cererile de participare și decide cu privire la posibilitatea aderării unei femei la Woman’s Hub, în conformitate cu scopurile și valorile platformei.'),
                        self::clause('5.5.',
                            'Порядок подачи и рассмотрения заявок, критерии отбора и порядок принятия решений могут быть определены отдельным регламентом.',
                            'The procedure for submitting and reviewing applications, the selection criteria and the decision-making procedure may be defined by a separate regulation.',
                            'Procedura de depunere și examinare a cererilor, criteriile de selecție și procedura de adoptare a deciziilor pot fi stabilite printr-un regulament separat.'),
                    ],
                ],
                [
                    'id' => 'packages',
                    'title' => self::t('6. Пакеты участия', '6. Participation packages', '6. Pachete de participare'),
                    'blocks' => [
                        self::package('community', '6.1.', 'WOMAN’S HUB COMMUNITY'),
                        self::package('active', '6.2.', 'WOMAN’S HUB ACTIVE', [
                            'Все преимущества пакета COMMUNITY.',
                            'All the benefits of the COMMUNITY package.',
                            'Toate avantajele pachetului COMMUNITY.',
                        ]),
                        self::package('private', '6.3.', 'WOMAN’S HUB PRIVATE', [
                            'Все преимущества пакетов COMMUNITY и ACTIVE.',
                            'All the benefits of the COMMUNITY and ACTIVE packages.',
                            'Toate avantajele pachetelor COMMUNITY și ACTIVE.',
                        ]),
                    ],
                ],
                [
                    'id' => 'gala',
                    'title' => self::t('7. Woman’s Hub и Гала Премия «Женщина Года»', '7. Woman’s Hub and the Gala “Woman of the Year”', '7. Woman’s Hub și Gala „Woman of the Year”'),
                    'blocks' => [
                        self::clause('7.1.',
                            'Участие в Гала Премии «Женщина Года» является базовой возможностью, предусмотренной всеми пакетами членства.',
                            'Participation in the Gala “Woman of the Year” is a basic opportunity provided by all membership packages.',
                            'Participarea la Gala „Woman of the Year” este o oportunitate de bază prevăzută de toate pachetele de membru.'),
                        self::clause('7.2.',
                            'Право на номинирование на премию предоставляется резидентам Woman’s Hub в соответствии с отдельным Положением о премии.',
                            'The right to nominate for the award is granted to residents of Woman’s Hub in accordance with the separate Regulations on the Award.',
                            'Dreptul de a nominaliza la premiu se acordă rezidentelor Woman’s Hub în conformitate cu Regulamentul separat privind premiul.'),
                        self::clause('7.3.',
                            'Категории, критерии, порядок выдвижения кандидатур, состав комиссии и порядок определения лауреатов регулируются отдельным Положением о премии.',
                            'The categories, criteria, nomination procedure, composition of the committee and the procedure for determining laureates are governed by the separate Regulations on the Award.',
                            'Categoriile, criteriile, procedura de nominalizare, componența comisiei și procedura de stabilire a laureatelor sunt reglementate de Regulamentul separat privind premiul.'),
                    ],
                ],
                [
                    'id' => 'rights',
                    'title' => self::t('8. Права и обязанности резидентов', '8. Rights and obligations of residents', '8. Drepturile și obligațiile rezidentelor'),
                    'blocks' => [
                        self::clause('8.1.',
                            'Резиденты имеют право получать информацию о деятельности Woman’s Hub, участвовать в мероприятиях и программах в соответствии с выбранным пакетом, пользоваться преимуществами членства, предлагать инициативы и участвовать в совместных проектах.',
                            'Residents have the right to receive information about the activities of Woman’s Hub, to take part in events and programs in accordance with the chosen package, to use the benefits of membership, to propose initiatives and to take part in joint projects.',
                            'Rezidentele au dreptul să primească informații despre activitatea Woman’s Hub, să participe la evenimente și programe în conformitate cu pachetul ales, să beneficieze de avantajele calității de membră, să propună inițiative și să participe la proiecte comune.'),
                        self::clause('8.2.',
                            'Резиденты обязуются соблюдать настоящее Положение и внутренние правила, уважительно относиться к другим участницам и партнёрам, соблюдать деловую этику и своевременно оплачивать членский взнос.',
                            'Residents undertake to comply with these Regulations and the internal rules, to treat other members and partners with respect, to observe business ethics and to pay the membership fee on time.',
                            'Rezidentele se obligă să respecte prezentul Regulament și regulile interne, să se raporteze cu respect față de celelalte participante și parteneri, să respecte etica în afaceri și să achite la timp cotizația de membru.'),
                    ],
                ],
                [
                    'id' => 'management',
                    'title' => self::t('9. Управление и развитие', '9. Management and development', '9. Conducere și dezvoltare'),
                    'blocks' => [
                        self::clause('9.1.',
                            'Координацию деятельности Woman’s Hub осуществляет уполномоченная сторона в рамках Агентства Инноваций и Развития.',
                            'The activities of Woman’s Hub are coordinated by an authorized party within the Agency for Innovation and Development.',
                            'Activitatea Woman’s Hub este coordonată de o parte împuternicită din cadrul Agenției pentru Inovare și Dezvoltare.'),
                        self::clause('9.2.',
                            'Программы, мероприятия, партнёрства, условия предоставления преимуществ и направления развития определяются настоящим Положением и отдельными внутренними документами.',
                            'Programs, events, partnerships, the conditions for granting benefits and the directions of development are determined by these Regulations and by separate internal documents.',
                            'Programele, evenimentele, parteneriatele, condițiile de acordare a avantajelor și direcțiile de dezvoltare sunt stabilite prin prezentul Regulament și prin documente interne separate.'),
                    ],
                ],
                [
                    'id' => 'final',
                    'title' => self::t('10. Заключительные положения', '10. Final provisions', '10. Dispoziții finale'),
                    'blocks' => [
                        self::clause('10.1.',
                            'Настоящее Положение определяет основные цели, принципы, организационную основу, членство и общие условия участия в Woman’s Hub.',
                            'These Regulations define the main goals, principles, organizational basis, membership and general conditions of participation in Woman’s Hub.',
                            'Prezentul Regulament stabilește scopurile principale, principiile, baza organizatorică, calitatea de membră și condițiile generale de participare la Woman’s Hub.'),
                        self::clause('10.2.',
                            'Конкретные процедурные вопросы и условия отдельных услуг могут регулироваться отдельными регламентами.',
                            'Specific procedural matters and the conditions of individual services may be governed by separate rules.',
                            'Aspectele procedurale concrete și condițiile unor servicii separate pot fi reglementate prin regulamente separate.'),
                        self::clause('10.3.',
                            'Изменения и дополнения в настоящее Положение утверждаются в установленном порядке.',
                            'Amendments and additions to these Regulations are approved in the established manner.',
                            'Modificările și completările la prezentul Regulament se aprobă în modul stabilit.'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Пакет участия для раздела 6: пункты берутся из lang/{ru,en,ro}/subscription.php, чтобы Положение, страница «Как
     * стать участницей» и кабинет не расходились. $first — вводная строка («Все преимущества пакета …»), если она нужна.
     *
     * @param  array{0: string, 1: string, 2: string}|null  $first
     * @return array<string, mixed>
     */
    private static function package(string $plan, string $number, string $name, ?array $first = null): array
    {
        $ru = __("subscription.plans.{$plan}.features", [], 'ru');
        $en = __("subscription.plans.{$plan}.features", [], 'en');
        $ro = __("subscription.plans.{$plan}.features", [], 'ro');

        $items = array_map(fn (int $i): array => [$ru[$i], $en[$i], $ro[$i]], array_keys($ru));

        if ($first !== null) {
            array_unshift($items, $first);
        }

        return ['type' => 'package', 'plan' => $plan, 'number' => $number, 'name' => $name, 'items' => self::triples($items)];
    }

    /** @return array{ru: string, en: string, ro: string} */
    private static function t(string $ru, string $en, string $ro): array
    {
        return ['ru' => $ru, 'en' => $en, 'ro' => $ro];
    }

    /** @return array<string, mixed> */
    private static function clause(string $number, string $ru, string $en, string $ro): array
    {
        return ['type' => 'clause', 'number' => $number, 'text' => self::t($ru, $en, $ro)];
    }

    /** @return array<string, mixed> */
    private static function lead(string $ru, string $en, string $ro): array
    {
        return ['type' => 'lead', 'text' => self::t($ru, $en, $ro)];
    }

    /**
     * Пункты списка из троек «русский, английский, румынский» в вид с ключами языков.
     *
     * @param  list<array{0: string, 1: string, 2: string}>  $items
     * @return list<array{ru: string, en: string, ro: string}>
     */
    private static function triples(array $items): array
    {
        return array_map(fn (array $i) => self::t(...$i), $items);
    }

    /**
     * @param  list<array{0: string, 1: string, 2: string}>  $items
     * @return array<string, mixed>
     */
    private static function bullets(array $items): array
    {
        return ['type' => 'list', 'items' => array_map(fn (array $i) => self::t(...$i), $items)];
    }
}
