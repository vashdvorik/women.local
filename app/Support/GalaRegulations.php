<?php

namespace App\Support;

/**
 * Текст «Положения о Гала-премии «Женщина года 2026»» (страница /gala/regulations) на трёх языках.
 *
 * Русский текст — дословно из документа docs/pologenie/Положение_Гала_премия_Женщина_года_2026_исправленное (2).docx;
 * английский и румынский — переводы. Заголовки разделов в документе набраны заглавными, на странице они даны обычным
 * регистром, как у Положения о платформе (App\Support\Regulations). Перевод юридического текста стоит показать юристу,
 * прежде чем считать его окончательным.
 *
 * Структура та же, что у Regulations (разделы с блоками clause / list), пакетов участия здесь нет.
 * Номер пункта с двоеточием в конце («2.2. Основные задачи премии:») идёт вводной фразой (clause), список — следующим блоком.
 */
final class GalaRegulations
{
    /**
     * @return array{
     *     title: array<string, string>, subtitle: array<string, string>, intro: array<string, string>, contents: array<string, string>,
     *     eyebrow: array<string, string>,
     *     sections: list<array{id: string, title: array<string, string>, blocks: list<array<string, mixed>>}>
     * }
     */
    public static function content(): array
    {
        return [
            'eyebrow' => self::t('Gala', 'Gala', 'Gala'),
            'title' => self::t(
                'Положение о Гала-премии «Женщина года 2026»',
                'Regulations of the Gala Award “Woman of the Year 2026”',
                'Regulamentul Gala-premiului „Femeia Anului 2026”',
            ),
            'subtitle' => self::t(
                'Дата проведения церемонии награждения: 4 декабря 2026 года',
                'Award ceremony date: 4 December 2026',
                'Data ceremoniei de decernare: 4 decembrie 2026',
            ),
            // Описание страницы для поисковиков и заголовка вкладки.
            'intro' => self::t(
                'Положение о Гала-премии «Женщина года 2026»: условия участия, порядок проведения, Комиссия, критерии оценки и лауреаты.',
                'Regulations of the Gala Award “Woman of the Year 2026”: conditions of participation, procedure, the Committee, evaluation criteria and laureates.',
                'Regulamentul Gala-premiului „Femeia Anului 2026”: condiții de participare, desfășurare, Comisia, criterii de evaluare și laureate.',
            ),
            'contents' => self::t('Содержание', 'Contents', 'Cuprins'),
            'sections' => [
                [
                    'id' => 'general',
                    'title' => self::t('1. Общие положения', '1. General provisions', '1. Dispoziții generale'),
                    'blocks' => [
                        self::clause('1.1.',
                            'Гала-премия «Женщина года 2026» является ежегодной премией, направленной на признание, поддержку и поощрение женщин, достигших значимых результатов в профессиональной, предпринимательской, общественной, социальной, культурной и иных сферах деятельности.',
                            'The Gala Award “Woman of the Year 2026” is an annual award aimed at recognizing, supporting and encouraging women who have achieved significant results in professional, entrepreneurial, public, social, cultural and other fields.',
                            'Gala-premiul „Femeia Anului 2026” este un premiu anual menit să recunoască, să sprijine și să încurajeze femeile care au obținut rezultate semnificative în domeniul profesional, antreprenorial, civic, social, cultural și în alte domenii de activitate.'),
                        self::clause('1.2.',
                            'Организатором Гала-премии «Женщина года 2026» является Некоммерческое партнерство «Агентство инноваций и развития».',
                            'The organizer of the Gala Award “Woman of the Year 2026” is the Non-profit Partnership “Agency for Innovation and Development”.',
                            'Organizatorul Gala-premiului „Femeia Anului 2026” este Parteneriatul nonprofit „Agenția pentru Inovare și Dezvoltare”.'),
                        self::clause('1.3.',
                            'В 2026 году Гала-премия проводится на базе Женской платформы и является одним из ключевых механизмов признания достижений ее участниц.',
                            'In 2026 the Gala Award is held on the basis of the Women’s Platform and is one of the key mechanisms for recognizing the achievements of its members.',
                            'În 2026 Gala-premiul se desfășoară în cadrul Platformei Femeilor și reprezintă unul dintre mecanismele-cheie de recunoaștere a realizărilor participantelor sale.'),
                        self::clause('1.4.',
                            'Торжественная церемония награждения лауреатов состоится 4 декабря 2026 года.',
                            'The solemn awards ceremony for the laureates will take place on 4 December 2026.',
                            'Ceremonia solemnă de decernare a premiilor către laureate va avea loc la 4 decembrie 2026.'),
                        self::clause('1.5.',
                            'Настоящее Положение определяет порядок участия в премии, подачи и рассмотрения заявок, принципы формирования Комиссии, порядок оценки кандидатур и определения лауреатов.',
                            'These Regulations define the procedure for participating in the award, submitting and reviewing applications, the principles of forming the Committee, the procedure for evaluating candidates and determining laureates.',
                            'Prezentul Regulament stabilește modul de participare la premiu, depunerea și examinarea cererilor, principiile de formare a Comisiei, procedura de evaluare a candidatelor și de stabilire a laureatelor.'),
                    ],
                ],
                [
                    'id' => 'purpose',
                    'title' => self::t('2. Цель и задачи премии', '2. Purpose and objectives of the award', '2. Scopul și obiectivele premiului'),
                    'blocks' => [
                        self::clause('2.1.',
                            'Цель премии: признание достижений женщин и их вклада в развитие бизнеса, общества, экономики, культуры, социальных инициатив и других сфер деятельности.',
                            'The purpose of the award: to recognize the achievements of women and their contribution to the development of business, society, the economy, culture, social initiatives and other fields.',
                            'Scopul premiului: recunoașterea realizărilor femeilor și a contribuției lor la dezvoltarea afacerilor, societății, economiei, culturii, inițiativelor sociale și a altor domenii de activitate.'),
                        self::clause('2.2.', 'Основные задачи премии:', 'The main objectives of the award:', 'Obiectivele principale ale premiului:'),
                        self::bullets([
                            ['выявление и признание женщин, достигших значимых результатов в своей деятельности;', 'identifying and recognizing women who have achieved significant results in their work;', 'identificarea și recunoașterea femeilor care au obținut rezultate semnificative în activitatea lor;'],
                            ['поддержка и продвижение активных участниц Женской платформы;', 'supporting and promoting active members of the Women’s Platform;', 'sprijinirea și promovarea participantelor active ale Platformei Femeilor;'],
                            ['повышение общественного признания достижений женщин;', 'raising public recognition of women’s achievements;', 'creșterea recunoașterii publice a realizărilor femeilor;'],
                            ['создание дополнительных возможностей для обмена опытом, развития партнерств и новых проектов;', 'creating additional opportunities for sharing experience, developing partnerships and new projects;', 'crearea de oportunități suplimentare pentru schimb de experiență, dezvoltarea parteneriatelor și a unor proiecte noi;'],
                            ['формирование сообщества женщин, способных своим примером вдохновлять других.', 'building a community of women who can inspire others by their example.', 'formarea unei comunități de femei capabile să inspire prin exemplul lor.'],
                        ]),
                    ],
                ],
                [
                    'id' => 'terms',
                    'title' => self::t('3. Условия участия', '3. Conditions of participation', '3. Condiții de participare'),
                    'blocks' => [
                        self::clause('3.1.',
                            'Право подать заявку на участие в Гала-премии «Женщина года 2026» имеют только женщины, являющиеся участницами Женской платформы.',
                            'Only women who are members of the Women’s Platform have the right to submit an application to participate in the Gala Award “Woman of the Year 2026”.',
                            'Au dreptul să depună o cerere de participare la Gala-premiul „Femeia Anului 2026” numai femeile care sunt participante ale Platformei Femeilor.'),
                        self::clause('3.2.',
                            'Участие в Женской платформе является обязательным условием для подачи заявки и рассмотрения кандидатуры в рамках премии.',
                            'Membership in the Women’s Platform is a mandatory condition for submitting an application and for having a candidacy considered within the award.',
                            'Calitatea de participantă a Platformei Femeilor este o condiție obligatorie pentru depunerea cererii și examinarea candidaturii în cadrul premiului.'),
                        self::clause('3.3.',
                            'Каждая участница Женской платформы имеет право подать заявку на участие в одной из утвержденных номинаций при условии соответствия требованиям данной номинации.',
                            'Each member of the Women’s Platform has the right to submit an application for one of the approved nominations, provided that she meets the requirements of that nomination.',
                            'Fiecare participantă a Platformei Femeilor are dreptul să depună o cerere pentru una dintre nominalizările aprobate, cu condiția să îndeplinească cerințele acesteia.'),
                        self::clause('3.4.',
                            'Участница, подавшая заявку, может претендовать на статус номинанта и лауреата премии в соответствии с результатами рассмотрения ее кандидатуры Комиссией.',
                            'A member who has submitted an application may claim the status of nominee and laureate of the award according to the results of the Committee’s review of her candidacy.',
                            'Participanta care a depus o cerere poate pretinde la statutul de nominalizată și laureată a premiului, conform rezultatelor examinării candidaturii sale de către Comisie.'),
                        self::clause('3.5.',
                            'Лауреатами Гала-премии «Женщина года 2026» могут стать исключительно участницы Женской платформы.',
                            'Only members of the Women’s Platform may become laureates of the Gala Award “Woman of the Year 2026”.',
                            'Laureate ale Gala-premiului „Femeia Anului 2026” pot deveni exclusiv participantele Platformei Femeilor.'),
                        self::clause('3.6.',
                            'Заявка подается в установленный организатором срок по утвержденной форме.',
                            'The application is submitted within the period set by the organizer, in the approved form.',
                            'Cererea se depune în termenul stabilit de organizator, după formularul aprobat.'),
                        self::clause('3.7.',
                            'Организатор вправе запросить дополнительные сведения и материалы, подтверждающие достижения и результаты, указанные в заявке.',
                            'The organizer has the right to request additional information and materials confirming the achievements and results stated in the application.',
                            'Organizatorul are dreptul să solicite informații și materiale suplimentare care confirmă realizările și rezultatele indicate în cerere.'),
                        self::clause('3.8.',
                            'В случае предоставления недостоверной информации кандидатура может быть исключена из дальнейшего рассмотрения.',
                            'If false information is provided, the candidacy may be excluded from further consideration.',
                            'În cazul furnizării de informații false, candidatura poate fi exclusă din examinarea ulterioară.'),
                    ],
                ],
                [
                    'id' => 'procedure',
                    'title' => self::t('4. Порядок проведения премии', '4. Procedure of the award', '4. Desfășurarea premiului'),
                    'blocks' => [
                        self::clause('4.1.', 'Проведение премии включает следующие основные этапы:', 'The award includes the following main stages:', 'Desfășurarea premiului include următoarele etape principale:'),
                        self::bullets([
                            ['объявление о начале приема заявок;', 'announcement of the start of the application period;', 'anunțarea începerii perioadei de depunere a cererilor;'],
                            ['прием заявок от участниц Женской платформы;', 'receiving applications from members of the Women’s Platform;', 'primirea cererilor de la participantele Platformei Femeilor;'],
                            ['проверка заявок на соответствие условиям участия;', 'checking applications for compliance with the conditions of participation;', 'verificarea conformității cererilor cu condițiile de participare;'],
                            ['рассмотрение и оценка кандидатур Комиссией;', 'review and evaluation of candidates by the Committee;', 'examinarea și evaluarea candidaturilor de către Comisie;'],
                            ['определение лауреатов по утвержденным номинациям;', 'determination of laureates in the approved nominations;', 'stabilirea laureatelor la nominalizările aprobate;'],
                            ['торжественная церемония награждения 4 декабря 2026 года.', 'the solemn awards ceremony on 4 December 2026.', 'ceremonia solemnă de decernare la 4 decembrie 2026.'],
                        ]),
                        self::clause('4.2.',
                            'Сроки приема заявок, перечень номинаций и дополнительные организационные условия утверждаются и публикуются организатором отдельно.',
                            'The deadlines for accepting applications, the list of nominations and additional organizational conditions are approved and published separately by the organizer.',
                            'Termenele de primire a cererilor, lista nominalizărilor și condițiile organizatorice suplimentare sunt aprobate și publicate separat de către organizator.'),
                    ],
                ],
                [
                    'id' => 'committee',
                    'title' => self::t('5. Комиссия по выбору лауреатов', '5. Committee for selecting laureates', '5. Comisia pentru selectarea laureatelor'),
                    'blocks' => [
                        self::clause('5.1.',
                            'Для рассмотрения заявок и определения лауреатов Гала-премии формируется Комиссия в составе 15 человек.',
                            'To review applications and determine the laureates of the Gala Award, a Committee of 15 people is formed.',
                            'Pentru examinarea cererilor și stabilirea laureatelor Gala-premiului se formează o Comisie în componență de 15 persoane.'),
                        self::clause('5.2.', 'В состав Комиссии входят:', 'The Committee consists of:', 'Comisia este formată din:'),
                        self::bullets([
                            ['5 амбассадоров премии, представляющих участниц и лауреатов Гала-премии прошлых лет;', '5 award ambassadors representing participants and laureates of the Gala Award of previous years;', '5 ambasadori ai premiului, care reprezintă participantele și laureatele Gala-premiului din anii precedenți;'],
                            ['5 представителей Некоммерческого партнерства «Агентство инноваций и развития»;', '5 representatives of the Non-profit Partnership “Agency for Innovation and Development”;', '5 reprezentanți ai Parteneriatului nonprofit „Agenția pentru Inovare și Dezvoltare”;'],
                            ['5 международных экспертов.', '5 international experts.', '5 experți internaționali.'],
                        ]),
                        self::clause('5.3.',
                            'Персональный состав Комиссии утверждается организатором премии.',
                            'The personal composition of the Committee is approved by the organizer of the award.',
                            'Componența nominală a Comisiei este aprobată de organizatorul premiului.'),
                        self::clause('5.4.',
                            'Комиссия рассматривает представленные заявки, оценивает достижения кандидаток и определяет лауреатов премии в соответствующих номинациях.',
                            'The Committee reviews the submitted applications, evaluates the candidates’ achievements and determines the laureates of the award in the relevant nominations.',
                            'Comisia examinează cererile depuse, evaluează realizările candidatelor și stabilește laureatele premiului la nominalizările respective.'),
                        self::clause('5.5.',
                            'Члены Комиссии руководствуются принципами объективности, профессиональной оценки, равного отношения к кандидаткам и конфиденциальности.',
                            'Committee members are guided by the principles of objectivity, professional assessment, equal treatment of candidates and confidentiality.',
                            'Membrii Comisiei se ghidează după principiile obiectivității, evaluării profesionale, tratamentului egal al candidatelor și confidențialității.'),
                        self::clause('5.6.',
                            'При наличии личной или иной заинтересованности, способной повлиять на объективность оценки конкретной кандидатки, член Комиссии обязан сообщить об этом и не участвовать в оценке данной кандидатуры.',
                            'If there is a personal or other interest that could affect the objectivity of the assessment of a particular candidate, a Committee member must disclose it and not take part in evaluating that candidacy.',
                            'În cazul existenței unui interes personal sau de altă natură, care ar putea afecta obiectivitatea evaluării unei candidate anume, membrul Comisiei este obligat să informeze despre aceasta și să nu participe la evaluarea acelei candidaturi.'),
                        self::clause('5.7.',
                            'Решение о лауреатах принимается Комиссией на основании результатов рассмотрения и оценки заявок в соответствии с утвержденным организатором порядком голосования или оценки.',
                            'The decision on the laureates is made by the Committee on the basis of the results of reviewing and evaluating applications, in accordance with the voting or evaluation procedure approved by the organizer.',
                            'Decizia privind laureatele este luată de Comisie pe baza rezultatelor examinării și evaluării cererilor, în conformitate cu procedura de vot sau de evaluare aprobată de organizator.'),
                    ],
                ],
                [
                    'id' => 'criteria',
                    'title' => self::t('6. Критерии оценки кандидатур', '6. Criteria for evaluating candidates', '6. Criterii de evaluare a candidaturilor'),
                    'blocks' => [
                        self::clause('6.1.',
                            'Комиссия оценивает реальные достижения и результаты деятельности кандидатки в рамках заявленной номинации.',
                            'The Committee evaluates the candidate’s real achievements and results within the nomination she has applied for.',
                            'Comisia evaluează realizările și rezultatele reale ale candidatei în cadrul nominalizării declarate.'),
                        self::clause('6.2.', 'При рассмотрении заявок могут учитываться:', 'When reviewing applications, the following may be taken into account:', 'La examinarea cererilor pot fi luate în considerare:'),
                        self::bullets([
                            ['значимость профессиональных, предпринимательских, общественных или иных достижений;', 'the significance of professional, entrepreneurial, public or other achievements;', 'semnificația realizărilor profesionale, antreprenoriale, civice sau de altă natură;'],
                            ['вклад кандидатки в развитие своей сферы деятельности;', 'the candidate’s contribution to the development of her field;', 'contribuția candidatei la dezvoltarea domeniului său de activitate;'],
                            ['устойчивость и практическая значимость достигнутых результатов;', 'the sustainability and practical significance of the results achieved;', 'stabilitatea și importanța practică a rezultatelor obținute;'],
                            ['лидерство, инициативность и способность создавать позитивные изменения;', 'leadership, initiative and the ability to create positive change;', 'leadership, spirit de inițiativă și capacitatea de a genera schimbări pozitive;'],
                            ['влияние деятельности кандидатки на людей, сообщество или развитие территории;', 'the impact of the candidate’s work on people, the community or the development of the area;', 'impactul activității candidatei asupra oamenilor, comunității sau dezvoltării teritoriului;'],
                            ['участие в деятельности и развитии Женской платформы.', 'participation in the activities and development of the Women’s Platform.', 'participarea la activitatea și dezvoltarea Platformei Femeilor.'],
                        ]),
                        self::clause('6.3.',
                            'Дополнительные критерии для отдельных номинаций могут утверждаться организатором отдельно.',
                            'Additional criteria for individual nominations may be approved separately by the organizer.',
                            'Criterii suplimentare pentru anumite nominalizări pot fi aprobate separat de către organizator.'),
                    ],
                ],
                [
                    'id' => 'laureates',
                    'title' => self::t('7. Лауреаты премии', '7. Laureates of the award', '7. Laureatele premiului'),
                    'blocks' => [
                        self::clause('7.1.',
                            'Лауреатами Гала-премии «Женщина года 2026» признаются участницы Женской платформы, определенные Комиссией победительницами в соответствующих номинациях.',
                            'Laureates of the Gala Award “Woman of the Year 2026” are members of the Women’s Platform whom the Committee has determined to be winners in the relevant nominations.',
                            'Laureate ale Gala-premiului „Femeia Anului 2026” sunt recunoscute participantele Platformei Femeilor, desemnate de Comisie câștigătoare la nominalizările respective.'),
                        self::clause('7.2.',
                            'Количество и наименования номинаций утверждаются организатором отдельно.',
                            'The number and names of the nominations are approved separately by the organizer.',
                            'Numărul și denumirile nominalizărilor sunt aprobate separat de către organizator.'),
                        self::clause('7.3.',
                            'Итоги работы Комиссии утверждаются организатором премии.',
                            'The results of the Committee’s work are approved by the organizer of the award.',
                            'Rezultatele activității Comisiei sunt aprobate de organizatorul premiului.'),
                        self::clause('7.4.',
                            'Имена лауреатов объявляются в рамках торжественной церемонии Гала-премии «Женщина года 2026», которая состоится 4 декабря 2026 года.',
                            'The names of the laureates are announced during the solemn ceremony of the Gala Award “Woman of the Year 2026”, which will take place on 4 December 2026.',
                            'Numele laureatelor sunt anunțate în cadrul ceremoniei solemne a Gala-premiului „Femeia Anului 2026”, care va avea loc la 4 decembrie 2026.'),
                    ],
                ],
                [
                    'id' => 'organizer',
                    'title' => self::t('8. Права и обязанности организатора', '8. Rights and obligations of the organizer', '8. Drepturile și obligațiile organizatorului'),
                    'blocks' => [
                        self::clause('8.1.',
                            'Организатор обеспечивает прием и обработку заявок, организацию работы Комиссии и проведение церемонии награждения.',
                            'The organizer ensures the receipt and processing of applications, the organization of the Committee’s work and the holding of the awards ceremony.',
                            'Organizatorul asigură primirea și prelucrarea cererilor, organizarea activității Comisiei și desfășurarea ceremoniei de decernare.'),
                        self::clause('8.2.',
                            'Организатор вправе устанавливать сроки, технические требования к заявкам и дополнительные организационные процедуры, необходимые для проведения премии.',
                            'The organizer has the right to set deadlines, technical requirements for applications and additional organizational procedures necessary for holding the award.',
                            'Organizatorul are dreptul să stabilească termene, cerințe tehnice pentru cereri și proceduri organizatorice suplimentare necesare pentru desfășurarea premiului.'),
                        self::clause('8.3.',
                            'При этом основополагающее условие участия остается неизменным: право подачи заявки и возможность стать лауреатом имеют исключительно участницы Женской платформы.',
                            'At the same time, the fundamental condition of participation remains unchanged: only members of the Women’s Platform have the right to submit an application and the opportunity to become a laureate.',
                            'Totodată, condiția fundamentală de participare rămâne neschimbată: dreptul de a depune o cerere și posibilitatea de a deveni laureată le au exclusiv participantele Platformei Femeilor.'),
                        self::clause('8.4.',
                            'Организатор вправе размещать информацию о номинантах и лауреатах на информационных ресурсах организатора, Женской платформы и партнеров премии.',
                            'The organizer has the right to publish information about nominees and laureates on the information resources of the organizer, the Women’s Platform and the partners of the award.',
                            'Organizatorul are dreptul să plaseze informații despre nominalizate și laureate pe resursele informaționale ale organizatorului, ale Platformei Femeilor și ale partenerilor premiului.'),
                    ],
                ],
                [
                    'id' => 'final',
                    'title' => self::t('9. Заключительные положения', '9. Final provisions', '9. Dispoziții finale'),
                    'blocks' => [
                        self::clause('9.1.',
                            'Настоящее Положение вступает в силу с момента его утверждения организатором.',
                            'These Regulations enter into force upon their approval by the organizer.',
                            'Prezentul Regulament intră în vigoare din momentul aprobării sale de către organizator.'),
                        self::clause('9.2.',
                            'Настоящее Положение действует в рамках проведения Гала-премии «Женщина года 2026».',
                            'These Regulations apply within the framework of holding the Gala Award “Woman of the Year 2026”.',
                            'Prezentul Regulament se aplică în cadrul desfășurării Gala-premiului „Femeia Anului 2026”.'),
                        self::clause('9.3.',
                            'Вопросы, не урегулированные настоящим Положением, решаются организатором с учетом целей премии и принципов объективности и равного отношения к участницам.',
                            'Matters not regulated by these Regulations are decided by the organizer, taking into account the goals of the award and the principles of objectivity and equal treatment of participants.',
                            'Chestiunile care nu sunt reglementate de prezentul Regulament sunt soluționate de organizator, ținând cont de scopurile premiului și de principiile obiectivității și tratamentului egal al participantelor.'),
                    ],
                ],
            ],
        ];
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

    /**
     * @param  list<array{0: string, 1: string, 2: string}>  $items
     * @return array<string, mixed>
     */
    private static function bullets(array $items): array
    {
        return ['type' => 'list', 'items' => array_map(fn (array $i) => self::t(...$i), $items)];
    }
}
