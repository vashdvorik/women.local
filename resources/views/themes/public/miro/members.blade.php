@php
    $botUrl = 'https://t.me/WomenComBot';
    $managerUrl = 'https://t.me/lesnichenkoP';
    $communityUrl = config('nutgram.community_url', $botUrl);

    // Every woman from the "Журнал общий — все проекты" archive (docs/women) who has a
    // real narrative write-up, minus every profile identified as a man. A missing photo
    // folder renders as a gradient initials avatar (see miro-participant-card__avatar--
    // placeholder below) instead of being skipped.
    $participants = [
        [
            'photo' => 'participants/participant-andreeva.jpg',
            'name' => ['ru' => 'Елена Андреева', 'en' => 'Elena Andreeva', 'ro' => 'Elena Andreeva'],
            'tag' => ['ru' => 'Производство', 'en' => 'Manufacturing', 'ro' => 'Producție'],
            'summary' => ['ru' => '«Биофрост» — шоковая заморозка хлебобулочных изделий.', 'en' => '"Biofrost" — flash-frozen bakery products.', 'ro' => '„Biofrost” — produse de panificație congelate rapid.'],
        ],
        [
            'photo' => 'participants/participant-arnaut-diana.jpg',
            'name' => ['ru' => 'Диана Арнаут', 'en' => 'Diana Arnaut', 'ro' => 'Diana Arnaut'],
            'tag' => ['ru' => 'Сельское хозяйство', 'en' => 'Agriculture', 'ro' => 'Agricultură'],
            'summary' => ['ru' => 'Фермер из Григориополя — выращивание малины на капельном орошении.', 'en' => 'A raspberry farm in Grigoriopol, grown with drip irrigation.', 'ro' => 'O fermă de zmeură la Grigoriopol, cu irigare prin picurare.'],
        ],
        [
            'photo' => 'participants/participant-balyka.jpg',
            'name' => ['ru' => 'Кристина Балыка', 'en' => 'Kristina Balyka', 'ro' => 'Kristina Balyka'],
            'tag' => ['ru' => 'Гостеприимство', 'en' => 'Hospitality', 'ro' => 'Ospitalitate'],
            'summary' => ['ru' => 'Capsula Hostel — первый капсульный отель в Приднестровье.', 'en' => 'Capsula Hostel — the first capsule hotel in Transnistria.', 'ro' => 'Capsula Hostel — primul hotel-capsulă din Transnistria.'],
        ],
        [
            'photo' => 'participants/participant-gavriluta.jpg',
            'name' => ['ru' => 'Анна Гаврилуца', 'en' => 'Anna Gavrilutsa', 'ro' => 'Anna Gavrilutsa'],
            'tag' => ['ru' => 'Сельское хозяйство', 'en' => 'Agriculture', 'ro' => 'Agricultură'],
            'summary' => ['ru' => 'VioBerry — тепличное выращивание клубники.', 'en' => 'VioBerry — greenhouse strawberry growing.', 'ro' => 'VioBerry — cultivarea căpșunilor în seră.'],
        ],
        [
            'photo' => 'participants/participant-galkina.jpg',
            'name' => ['ru' => 'Арина Галкина', 'en' => 'Arina Galkina', 'ro' => 'Arina Galkina'],
            'tag' => ['ru' => 'Эко-товары', 'en' => 'Eco goods', 'ro' => 'Produse eco'],
            'summary' => ['ru' => '«ЭКО торба» и Goodwin.glass — эко-товары ручной работы.', 'en' => '"ECO torba" and Goodwin.glass — handmade eco goods.', 'ro' => '„ECO torba” și Goodwin.glass — produse eco lucrate manual.'],
        ],
        [
            'photo' => null,
            'name' => ['ru' => 'Ирина Емельянова', 'en' => 'Irina Emelyanova', 'ro' => 'Irina Emelyanova'],
            'tag' => ['ru' => 'Туризм', 'en' => 'Tourism', 'ro' => 'Turism'],
            'summary' => ['ru' => 'Глэмпинг — комфортный отдых на природе у Днестра.', 'en' => 'A glamping site blending hotel comfort with nature by the Dniester.', 'ro' => 'Un glamping care îmbină confortul unui hotel cu natura, lângă Nistru.'],
        ],
        [
            'photo' => 'participants/participant-koshnianu.jpg',
            'name' => ['ru' => 'Марина Кошняну', 'en' => 'Marina Koshnyanu', 'ro' => 'Marina Koshnyanu'],
            'tag' => ['ru' => 'Гостеприимство', 'en' => 'Hospitality', 'ro' => 'Ospitalitate'],
            'summary' => ['ru' => 'Гостевой дом в Моловате на берегу Днестра.', 'en' => 'A guest house in Molovata on the Dniester riverbank.', 'ro' => 'O casă de oaspeți în Molovata, pe malul Nistrului.'],
        ],
        [
            'photo' => 'participants/participant-kyrlan.jpg',
            'name' => ['ru' => 'Анна Кырлан', 'en' => 'Anna Kyrlan', 'ro' => 'Anna Kyrlan'],
            'tag' => ['ru' => 'Сельское хозяйство', 'en' => 'Agriculture', 'ro' => 'Agricultură'],
            'summary' => ['ru' => 'Фермер — выращивание винограда на правом берегу.', 'en' => 'Farmer — vineyard on the right bank.', 'ro' => 'Fermieră — viticultură pe malul drept.'],
        ],
        [
            'photo' => null,
            'name' => ['ru' => 'Алена Матей', 'en' => 'Alena Matey', 'ro' => 'Alena Matey'],
            'tag' => ['ru' => 'Производство', 'en' => 'Manufacturing', 'ro' => 'Producție'],
            'summary' => ['ru' => '«Yunna-Plant» — мини-завод натуральных сиропов и соков холодного отжима.', 'en' => '"Yunna-Plant" — a micro-plant for natural syrups and cold-pressed juices.', 'ro' => '„Yunna-Plant” — o mini-fabrică de siropuri naturale și sucuri presate la rece.'],
        ],
        [
            'photo' => 'participants/participant-postika.jpg',
            'name' => ['ru' => 'Ирина Постика', 'en' => 'Irina Postika', 'ro' => 'Irina Postika'],
            'tag' => ['ru' => 'Гостеприимство', 'en' => 'Hospitality', 'ro' => 'Ospitalitate'],
            'summary' => ['ru' => 'Гостевой дом «MUZE» — отель, кафе и арт-галерея.', 'en' => '"MUZE" — a guest house with a café and art gallery.', 'ro' => '„MUZE” — casă de oaspeți cu cafenea și galerie de artă.'],
        ],
        [
            'photo' => 'participants/participant-rozhenko-elena.jpg',
            'name' => ['ru' => 'Елена Роженко', 'en' => 'Elena Rozhenko', 'ro' => 'Elena Rozhenko'],
            'tag' => ['ru' => 'Красота', 'en' => 'Beauty', 'ro' => 'Frumusețe'],
            'summary' => ['ru' => '«ФЁКЛА» — органическая косметика из натуральных трав и масел.', 'en' => '"FEKLA" — organic cosmetics made from natural herbs and oils.', 'ro' => '„FEKLA” — cosmetice organice din plante și uleiuri naturale.'],
        ],
        [
            'photo' => 'participants/participant-tabunchik-marina.jpg',
            'name' => ['ru' => 'Марина Табунчик', 'en' => 'Marina Tabunchik', 'ro' => 'Marina Tabunchik'],
            'tag' => ['ru' => 'Мода', 'en' => 'Fashion', 'ro' => 'Modă'],
            'summary' => ['ru' => 'Accent Textile — детская одежда из органического хлопка.', 'en' => '"Accent Textile" — children\'s clothing made from organic cotton.', 'ro' => '„Accent Textile” — haine pentru copii din bumbac organic.'],
        ],
        [
            'photo' => 'participants/participant-cherkasenko-marina.jpg',
            'name' => ['ru' => 'Марина Черкасенко', 'en' => 'Marina Cherkasenko', 'ro' => 'Marina Cherkasenko'],
            'tag' => ['ru' => 'Сельское хозяйство', 'en' => 'Agriculture', 'ro' => 'Agricultură'],
            'summary' => ['ru' => 'Крестьянско-фермерское хозяйство — 66 га зерновых на левом берегу Днестра.', 'en' => 'A farm growing grain crops on 66 hectares by the Dniester.', 'ro' => 'O fermă cu 66 ha de cereale pe malul stâng al Nistrului.'],
        ],
        [
            'photo' => 'participants/participant-shchukina-olga.jpg',
            'name' => ['ru' => 'Ольга Щукина', 'en' => 'Olga Shchukina', 'ro' => 'Olga Shchukina'],
            'tag' => ['ru' => 'Образование', 'en' => 'Education', 'ro' => 'Educație'],
            'summary' => ['ru' => 'Консалтинговый центр — профориентация и международное образование для молодёжи.', 'en' => 'A consulting centre for career guidance and international education.', 'ro' => 'Un centru de consiliere pentru orientare profesională și studii internaționale.'],
        ],
        [
            'photo' => 'participants/participant-boynegri-irina.jpg',
            'name' => ['ru' => 'Ирина Бойнегри', 'en' => 'Irina Boynegri', 'ro' => 'Irina Boynegri'],
            'tag' => ['ru' => 'Здоровье и фитнес', 'en' => 'Health & fitness', 'ro' => 'Sănătate și fitness'],
            'summary' => ['ru' => 'TERRA-fit — студия ЭМС-тренировок и реабилитационного фитнеса.', 'en' => '"TERRA-fit" — an EMS training and rehabilitation fitness studio.', 'ro' => '„TERRA-fit” — un studio de antrenamente EMS și fitness de recuperare.'],
        ],
        [
            'photo' => 'participants/participant-vetrova-anna.jpg',
            'name' => ['ru' => 'Анна Ветрова', 'en' => 'Anna Vetrova', 'ro' => 'Anna Vetrova'],
            'tag' => ['ru' => 'Производство', 'en' => 'Manufacturing', 'ro' => 'Producție'],
            'summary' => ['ru' => 'Шоковая заморозка овощей, фруктов и ягод от местных фермеров.', 'en' => 'Flash-frozen fruit, vegetables and berries sourced from local farmers.', 'ro' => 'Fructe, legume și fructe de pădure congelate rapid, de la fermieri locali.'],
        ],
        [
            'photo' => 'participants/participant-volskaya-anastasiya.jpg',
            'name' => ['ru' => 'Анастасия Вольская', 'en' => 'Anastasiya Volskaya', 'ro' => 'Anastasiya Volskaya'],
            'tag' => ['ru' => 'IT', 'en' => 'IT', 'ro' => 'IT'],
            'summary' => ['ru' => 'IT-предприятие — мониторинг полей дронами для аграриев.', 'en' => 'A drone-based field monitoring service for farmers.', 'ro' => 'Un serviciu de monitorizare a câmpurilor cu drone pentru fermieri.'],
        ],
        [
            'photo' => 'participants/participant-gratilova-natalya.jpg',
            'name' => ['ru' => 'Наталья Гратилова', 'en' => 'Natalya Gratilova', 'ro' => 'Natalya Gratilova'],
            'tag' => ['ru' => 'Сельское хозяйство', 'en' => 'Agriculture', 'ro' => 'Agricultură'],
            'summary' => ['ru' => 'Проростки и микрозелень — пшеница, маш, чечевица, гречка.', 'en' => 'Sprouts and microgreens — wheat, mung bean, lentil, buckwheat.', 'ro' => 'Germeni și microverdețuri — grâu, mung, linte, hrișcă.'],
        ],
        [
            'photo' => 'participants/participant-zatyka-alina.jpg',
            'name' => ['ru' => 'Алина Затыка', 'en' => 'Alina Zatyka', 'ro' => 'Alina Zatyka'],
            'tag' => ['ru' => 'Пчеловодство', 'en' => 'Beekeeping', 'ro' => 'Apicultură'],
            'summary' => ['ru' => 'Семейная пасека — мёд, пыльца, прополис и воск.', 'en' => 'A family apiary producing honey, pollen, propolis and wax.', 'ro' => 'O stupină de familie — miere, polen, propolis și ceară.'],
        ],
        [
            'photo' => 'participants/participant-lashko-anna.jpg',
            'name' => ['ru' => 'Анна Лашко', 'en' => 'Anna Lashko', 'ro' => 'Anna Lashko'],
            'tag' => ['ru' => 'Общепит', 'en' => 'Food service', 'ro' => 'Alimentație'],
            'summary' => ['ru' => 'Итальянское мороженое «Джелато» из свежего молдавского сырья.', 'en' => 'Italian "Gelato" ice cream made with fresh local ingredients.', 'ro' => '„Gelato” — înghețată italiană din ingrediente locale proaspete.'],
        ],
        [
            'photo' => null,
            'name' => ['ru' => 'Ксения Мигова', 'en' => 'Kseniya Migova', 'ro' => 'Kseniya Migova'],
            'tag' => ['ru' => 'Медиапродакшн', 'en' => 'Media production', 'ro' => 'Producție media'],
            'summary' => ['ru' => '«POZITIVE» — видеостудия для бизнеса и мероприятий.', 'en' => '"POZITIVE" — a video production studio for business and events.', 'ro' => '„POZITIVE” — un studio video pentru afaceri și evenimente.'],
        ],
        [
            'photo' => 'participants/participant-mileva-dmitrishina-mariya.jpg',
            'name' => ['ru' => 'Мария Милева-Дмитришина', 'en' => 'Mariya Mileva-Dmitrishina', 'ro' => 'Mariya Mileva-Dmitrishina'],
            'tag' => ['ru' => 'Сыроварение', 'en' => 'Cheesemaking', 'ro' => 'Fabricarea brânzei'],
            'summary' => ['ru' => 'Крафтовые сыры по европейским технологиям из приднестровского молока.', 'en' => 'Craft cheeses made by European methods from local milk.', 'ro' => 'Brânzeturi artizanale, după tehnologii europene, din lapte local.'],
        ],
        [
            'photo' => 'participants/participant-pikul-inna.jpg',
            'name' => ['ru' => 'Инна Пикул', 'en' => 'Inna Pikul', 'ro' => 'Inna Pikul'],
            'tag' => ['ru' => 'Общепит', 'en' => 'Food service', 'ro' => 'Alimentație'],
            'summary' => ['ru' => 'Кафе-блинная — блины «как у бабушки» в формате фастфуда.', 'en' => 'A pancake café — "grandma\'s" blini, quick-service style.', 'ro' => 'O cafenea cu clătite — „ca la bunica”, în stil fast-food.'],
        ],
        [
            'photo' => null,
            'name' => ['ru' => 'Ирина Носкова', 'en' => 'Irina Noskova', 'ro' => 'Irina Noskova'],
            'tag' => ['ru' => 'Предпринимательство', 'en' => 'Entrepreneurship', 'ro' => 'Antreprenoriat'],
            'summary' => ['ru' => 'Участница программы AdTrade, 2022 год.', 'en' => 'AdTrade programme participant, 2022.', 'ro' => 'Participantă la programul AdTrade, 2022.'],
        ],
        [
            'photo' => 'participants/participant-balan-natalya.jpg',
            'name' => ['ru' => 'Наталья Балан', 'en' => 'Natalya Balan', 'ro' => 'Natalya Balan'],
            'tag' => ['ru' => 'Красота', 'en' => 'Beauty', 'ro' => 'Frumusețe'],
            'summary' => ['ru' => 'Социальная парикмахерская — льготные услуги для незащищённых семей.', 'en' => 'A social hair salon offering discounted care for vulnerable families.', 'ro' => 'O frizerie socială cu servicii la preț redus pentru familii vulnerabile.'],
        ],
        [
            'photo' => 'participants/participant-grechko-yuliya.jpg',
            'name' => ['ru' => 'Юлия Гречко', 'en' => 'Yuliya Grechko', 'ro' => 'Yuliya Grechko'],
            'tag' => ['ru' => 'Образование', 'en' => 'Education', 'ro' => 'Educație'],
            'summary' => ['ru' => '«CREATIVITY» — центр культурно-развлекательных инициатив для детей и взрослых.', 'en' => '"CREATIVITY" — a cultural and recreation centre for kids and adults.', 'ro' => '„CREATIVITY” — un centru cultural și de agrement pentru copii și adulți.'],
        ],
        [
            'photo' => 'participants/participant-novikova-tatyana.jpg',
            'name' => ['ru' => 'Татьяна Новикова', 'en' => 'Tatyana Novikova', 'ro' => 'Tatyana Novikova'],
            'tag' => ['ru' => 'Производство', 'en' => 'Manufacturing', 'ro' => 'Producție'],
            'summary' => ['ru' => 'УПП «Рассвет» — товары народного потребления из вторсырья.', 'en' => '"Rassvet" — household plastic goods made from recycled material.', 'ro' => '„Rassvet” — produse de larg consum din materiale reciclate.'],
        ],
        [
            'photo' => null,
            'name' => ['ru' => 'Татьяна Алексей', 'en' => 'Tatyana Aleksey', 'ro' => 'Tatyana Aleksey'],
            'tag' => ['ru' => 'Животноводство', 'en' => 'Livestock', 'ro' => 'Zootehnie'],
            'summary' => ['ru' => 'Птицеводство — экологически чистые яйца и мясо птицы в Карманово.', 'en' => 'A poultry farm — eco-friendly eggs and meat in Karmanovo.', 'ro' => 'O fermă avicolă — ouă și carne ecologice în Karmanovo.'],
        ],
        [
            'photo' => 'participants/participant-alishevich-yuliya.jpg',
            'name' => ['ru' => 'Юлия Алишевич', 'en' => 'Yuliya Alishevich', 'ro' => 'Yuliya Alishevich'],
            'tag' => ['ru' => 'Производство', 'en' => 'Manufacturing', 'ro' => 'Producție'],
            'summary' => ['ru' => 'Tatti Lux — премиальные приправы без соли и добавок.', 'en' => '"Tatti Lux" — premium salt-free seasonings with no additives.', 'ro' => '„Tatti Lux” — condimente premium, fără sare și aditivi.'],
        ],
        [
            'photo' => 'participants/participant-gaypel-nadezhda.jpg',
            'name' => ['ru' => 'Надежда Гайпель', 'en' => 'Nadezhda Gaypel', 'ro' => 'Nadezhda Gaypel'],
            'tag' => ['ru' => 'Кондитерское дело', 'en' => 'Confectionery', 'ro' => 'Cofetărie'],
            'summary' => ['ru' => '«Фрея» — натуральные снеки: пастила, сухофрукты, ореховая паста.', 'en' => '"Freya" — natural snacks: fruit pastila, dried fruit, nut butter.', 'ro' => '„Freya” — gustări naturale: pastilă de fructe, fructe uscate, unt de nuci.'],
        ],
        [
            'photo' => 'participants/participant-dmitrishina-svetlana.jpg',
            'name' => ['ru' => 'Светлана Дмитришина', 'en' => 'Svetlana Dmitrishina', 'ro' => 'Svetlana Dmitrishina'],
            'tag' => ['ru' => 'Кондитерское дело', 'en' => 'Confectionery', 'ro' => 'Cofetărie'],
            'summary' => ['ru' => '«Маруся» — натуральные джемы: тыква, слива-чабрец, айва, инжир.', 'en' => '"Marusya" — natural jams: pumpkin, plum-thyme, quince, fig.', 'ro' => '„Marusya” — gemuri naturale: dovleac, prună-cimbru, gutuie, smochine.'],
        ],
        [
            'photo' => 'participants/participant-dubin-lidiya.jpg',
            'name' => ['ru' => 'Лидия Дубин', 'en' => 'Lidiya Dubin', 'ro' => 'Lidiya Dubin'],
            'tag' => ['ru' => 'Производство', 'en' => 'Manufacturing', 'ro' => 'Producție'],
            'summary' => ['ru' => 'Steelwice Engineering — механические компоненты для промышленности.', 'en' => '"Steelwice Engineering" — precision mechanical components for industry.', 'ro' => '„Steelwice Engineering” — componente mecanice de precizie pentru industrie.'],
        ],
        [
            'photo' => 'participants/participant-kazaku-irina.jpg',
            'name' => ['ru' => 'Ирина Казаку', 'en' => 'Irina Kazaku', 'ro' => 'Irina Kazaku'],
            'tag' => ['ru' => 'Кондитерское дело', 'en' => 'Confectionery', 'ro' => 'Cofetărie'],
            'summary' => ['ru' => 'Ice Dessert — десерты шоковой заморозки без глютена и лактозы.', 'en' => '"Ice Dessert" — flash-frozen desserts, gluten- and lactose-free.', 'ro' => '„Ice Dessert” — deserturi congelate rapid, fără gluten și lactoză.'],
        ],
        [
            'photo' => 'participants/participant-kozhokaru-anastasiya.jpg',
            'name' => ['ru' => 'Анастасия Кожокару', 'en' => 'Anastasiya Kozhokaru', 'ro' => 'Anastasiya Kozhokaru'],
            'tag' => ['ru' => 'Мода', 'en' => 'Fashion', 'ro' => 'Modă'],
            'summary' => ['ru' => 'Fitness Mafia Shop — ателье спортивной одежды для девушек.', 'en' => '"Fitness Mafia Shop" — a sportswear atelier for women.', 'ro' => '„Fitness Mafia Shop” — un atelier de îmbrăcăminte sport pentru femei.'],
        ],
        [
            'photo' => 'participants/participant-kyvyrzhik-inna.jpg',
            'name' => ['ru' => 'Инна Кывыржик', 'en' => 'Inna Kyvyrzhik', 'ro' => 'Inna Kyvyrzhik'],
            'tag' => ['ru' => 'Кондитерское дело', 'en' => 'Confectionery', 'ro' => 'Cofetărie'],
            'summary' => ['ru' => 'Sweetland — шоколад и медовые подарочные боксы.', 'en' => '"Sweetland" — chocolate and honey gift boxes.', 'ro' => '„Sweetland” — cutii cadou cu ciocolată și miere.'],
        ],
        [
            'photo' => 'participants/participant-lobacheva-ekaterina.jpg',
            'name' => ['ru' => 'Екатерина Лобачева', 'en' => 'Ekaterina Lobacheva', 'ro' => 'Ekaterina Lobacheva'],
            'tag' => ['ru' => 'Производство', 'en' => 'Manufacturing', 'ro' => 'Producție'],
            'summary' => ['ru' => '«Ароматные травы» — бальзамический уксус и травяные настойки.', 'en' => '"Fragrant Herbs" — balsamic vinegar and herbal tinctures.', 'ro' => '„Ierburi Aromate” — oțet balsamic și tincturi din plante.'],
        ],
        [
            'photo' => 'participants/participant-neutova-olga.jpg',
            'name' => ['ru' => 'Ольга Неутова', 'en' => 'Olga Neutova', 'ro' => 'Olga Neutova'],
            'tag' => ['ru' => 'Кондитерское дело', 'en' => 'Confectionery', 'ro' => 'Cofetărie'],
            'summary' => ['ru' => '«Mom Shef» — расписные пряники ручной работы.', 'en' => '"Mom Shef" — hand-painted, handmade gingerbread.', 'ro' => '„Mom Shef” — turtă dulce pictată manual.'],
        ],
        [
            'photo' => 'participants/participant-palamar-natalya.jpg',
            'name' => ['ru' => 'Наталья Паламар', 'en' => 'Natalya Palamar', 'ro' => 'Natalya Palamar'],
            'tag' => ['ru' => 'IT', 'en' => 'IT', 'ro' => 'IT'],
            'summary' => ['ru' => 'Kvazar-Micro — IT-сервис мониторинга микроклимата и техники.', 'en' => '"Kvazar-Micro" — an IT service for climate and equipment monitoring.', 'ro' => '„Kvazar-Micro” — un serviciu IT de monitorizare a climatului și echipamentelor.'],
        ],
        [
            'photo' => 'participants/participant-panga-natalya.jpg',
            'name' => ['ru' => 'Наталья Панга', 'en' => 'Natalya Panga', 'ro' => 'Natalya Panga'],
            'tag' => ['ru' => 'Кондитерское дело', 'en' => 'Confectionery', 'ro' => 'Cofetărie'],
            'summary' => ['ru' => '«Пряничный домик» — выпечка на мёде, шоколаде и травах.', 'en' => '"Gingerbread House" — pastries made with honey, chocolate and herbs.', 'ro' => '„Căsuța de Turtă Dulce” — produse de patiserie cu miere, ciocolată și plante.'],
        ],
        [
            'photo' => 'participants/participant-panfiliy-diana.jpg',
            'name' => ['ru' => 'Диана Панфилий', 'en' => 'Diana Panfiliy', 'ro' => 'Diana Panfiliy'],
            'tag' => ['ru' => 'Гостеприимство', 'en' => 'Hospitality', 'ro' => 'Ospitalitate'],
            'summary' => ['ru' => 'Гостевой дом в Вадул-Рашкове — молдавские традиции у Днестра.', 'en' => 'A guest house in Vadul-Rașcov, steeped in Moldovan tradition by the Dniester.', 'ro' => 'O casă de oaspeți la Vadul-Rașcov, cu tradiții moldovenești pe malul Nistrului.'],
        ],
        [
            'photo' => 'participants/participant-porhun-natalya.jpg',
            'name' => ['ru' => 'Наталья Порхун', 'en' => 'Natalya Porhun', 'ro' => 'Natalya Porhun'],
            'tag' => ['ru' => 'Пчеловодство', 'en' => 'Beekeeping', 'ro' => 'Apicultură'],
            'summary' => ['ru' => '«Ложка мёда» — семейная пасека, мёд и продукты пчеловодства.', 'en' => '"A Spoonful of Honey" — a family apiary and bee products.', 'ro' => '„O Lingură de Miere” — o stupină de familie și produse apicole.'],
        ],
        [
            'photo' => 'participants/participant-rusnak-ekaterina.jpg',
            'name' => ['ru' => 'Екатерина Руснак', 'en' => 'Ekaterina Rusnak', 'ro' => 'Ekaterina Rusnak'],
            'tag' => ['ru' => 'Производство', 'en' => 'Manufacturing', 'ro' => 'Producție'],
            'summary' => ['ru' => 'Натуральные соки прямого отжима из семейного сада и виноградника.', 'en' => 'Cold-pressed natural juices from the family orchard and vineyard.', 'ro' => 'Sucuri naturale presate din livada și via familiei.'],
        ],
        [
            'photo' => 'participants/participant-shvets-elena.jpg',
            'name' => ['ru' => 'Елена Швец', 'en' => 'Elena Shvets', 'ro' => 'Elena Shvets'],
            'tag' => ['ru' => 'Пчеловодство', 'en' => 'Beekeeping', 'ro' => 'Apicultură'],
            'summary' => ['ru' => 'Продукты пчеловодства и апидомик для апитерапии.', 'en' => 'Bee products and an api-house for apitherapy relaxation.', 'ro' => 'Produse apicole și o casă apiterapeutică pentru relaxare.'],
        ],
        [
            'photo' => 'participants/participant-vyushkova-vladlena.jpg',
            'name' => ['ru' => 'Владлена Вьюшкова', 'en' => 'Vladlena Vyushkova', 'ro' => 'Vladlena Vyushkova'],
            'tag' => ['ru' => 'Текстиль', 'en' => 'Textiles', 'ro' => 'Textile'],
            'summary' => ['ru' => '«Вышивальная сказка» — авторская вышивка на эко-одежде.', 'en' => '"Embroidery Tale" — original embroidery on eco-friendly clothing.', 'ro' => '„Povestea Brodată” — broderie originală pe haine ecologice.'],
        ],
        [
            'photo' => 'participants/participant-doni-elena.jpg',
            'name' => ['ru' => 'Елена Дони', 'en' => 'Elena Doni', 'ro' => 'Elena Doni'],
            'tag' => ['ru' => 'Красота', 'en' => 'Beauty', 'ro' => 'Frumusețe'],
            'summary' => ['ru' => 'Инновационный центр красоты — лифтинг и антистресс-терапия.', 'en' => 'An innovative beauty centre — lifting and anti-stress therapy.', 'ro' => 'Un centru inovator de înfrumusețare — lifting și terapie anti-stres.'],
        ],
        [
            'photo' => 'participants/participant-ivanova-elena.jpg',
            'name' => ['ru' => 'Елена Иванова', 'en' => 'Elena Ivanova', 'ro' => 'Elena Ivanova'],
            'tag' => ['ru' => 'Сельское хозяйство', 'en' => 'Agriculture', 'ro' => 'Agricultură'],
            'summary' => ['ru' => '«Малиновый рай» — выращивание и переработка малины.', 'en' => '"Raspberry Paradise" — growing and processing raspberries.', 'ro' => '„Raiul Zmeurei” — cultivarea și procesarea zmeurei.'],
        ],
        [
            'photo' => 'participants/participant-kozhokar-viktoriya.jpg',
            'name' => ['ru' => 'Виктория Кожокарь', 'en' => 'Viktoriya Kozhokar', 'ro' => 'Viktoriya Kozhokar'],
            'tag' => ['ru' => 'Пчеловодство', 'en' => 'Beekeeping', 'ro' => 'Apicultură'],
            'summary' => ['ru' => 'Пасека в Пырыте — пыльца, прополис и маточное молочко на экспорт.', 'en' => 'An apiary in Pyrita — pollen, propolis and royal jelly for export.', 'ro' => 'O stupină la Pirâta — polen, propolis și lăptișor de matcă pentru export.'],
        ],
        [
            'photo' => null,
            'name' => ['ru' => 'Дарья Крайняя', 'en' => 'Darya Kraynyaya', 'ro' => 'Darya Kraynyaya'],
            'tag' => ['ru' => 'Сельское хозяйство', 'en' => 'Agriculture', 'ro' => 'Agricultură'],
            'summary' => ['ru' => 'Крестьянско-фермерское хозяйство — выращивание фундука.', 'en' => 'A farm growing hazelnuts.', 'ro' => 'O fermă de cultivare a alunelor.'],
        ],
        [
            'photo' => 'participants/participant-kyshlar-tatyana.jpg',
            'name' => ['ru' => 'Татьяна Кышларь', 'en' => 'Tatyana Kyshlar', 'ro' => 'Tatyana Kyshlar'],
            'tag' => ['ru' => 'Сельское хозяйство', 'en' => 'Agriculture', 'ro' => 'Agricultură'],
            'summary' => ['ru' => 'Энергонезависимый холодильный склад на 80 тонн для экспорта фруктов.', 'en' => 'An 80-tonne solar-powered cold store for fruit export.', 'ro' => 'Un depozit frigorific de 80 tone, pe energie solară, pentru export de fructe.'],
        ],
        [
            'photo' => 'participants/participant-mushinski-viktoriya.jpg',
            'name' => ['ru' => 'Виктория Мушински', 'en' => 'Viktoriya Mushinski', 'ro' => 'Viktoriya Mushinski'],
            'tag' => ['ru' => 'Туризм', 'en' => 'Tourism', 'ro' => 'Turism'],
            'summary' => ['ru' => '«Приключение на Днестре» — экологичные прогулки на понтонном катере.', 'en' => '"Dniester Adventure" — eco-friendly pontoon boat river tours.', 'ro' => '„Aventura pe Nistru” — plimbări ecologice cu barca-pontoane.'],
        ],
        [
            'photo' => 'participants/participant-solovtsova-sofiya.jpg',
            'name' => ['ru' => 'София Соловцова', 'en' => 'Sofiya Solovtsova', 'ro' => 'Sofiya Solovtsova'],
            'tag' => ['ru' => 'Кондитерское дело', 'en' => 'Confectionery', 'ro' => 'Cofetărie'],
            'summary' => ['ru' => '«Татарочка» — кондитерская студия в стадии запуска.', 'en' => '"Tatarochka" — a confectionery studio now in launch.', 'ro' => '„Tatarochka” — un studio de cofetărie în curs de lansare.'],
        ],
        [
            'photo' => 'participants/participant-chernyatinskaya-natalya.jpg',
            'name' => ['ru' => 'Наталья Чернятинская', 'en' => 'Natalya Chernyatinskaya', 'ro' => 'Natalya Chernyatinskaya'],
            'tag' => ['ru' => 'Мода', 'en' => 'Fashion', 'ro' => 'Modă'],
            'summary' => ['ru' => '«GALLERY.21» — капсульный гардероб из переработанных тканей.', 'en' => '"GALLERY.21" — a capsule wardrobe made from recycled fabrics.', 'ro' => '„GALLERY.21” — o garderobă capsulă din materiale reciclate.'],
        ],
        [
            'photo' => 'participants/participant-belobrova-nina.jpg',
            'name' => ['ru' => 'Нина Белоброва', 'en' => 'Nina Belobrova', 'ro' => 'Nina Belobrova'],
            'tag' => ['ru' => 'Общепит', 'en' => 'Food service', 'ro' => 'Alimentație'],
            'summary' => ['ru' => '«Блинок-ОК!» — блины с начинками, цех и выездная торговля.', 'en' => '"Blinok-OK!" — filled pancakes, with a workshop and mobile stalls.', 'ro' => '„Blinok-OK!” — clătite cu umpluturi, atelier și vânzare ambulantă.'],
        ],
        [
            'photo' => null,
            'name' => ['ru' => 'Ольга Карайман', 'en' => 'Olga Karayman', 'ro' => 'Olga Karayman'],
            'tag' => ['ru' => 'Общепит', 'en' => 'Food service', 'ro' => 'Alimentație'],
            'summary' => ['ru' => 'Кафе-кондитерская в Новой Моловате — расширение семейного хозяйства в HoReCa.', 'en' => 'A café-patisserie in Novaya Molovata, growing the family farm into HoReCa.', 'ro' => 'O cafenea-cofetărie în Novaia Molovata, extinzând ferma familiei în HoReCa.'],
        ],
        [
            'photo' => 'participants/participant-mastak-marichika.jpg',
            'name' => ['ru' => 'Маричика Мастак', 'en' => 'Marichika Mastak', 'ro' => 'Marichika Mastak'],
            'tag' => ['ru' => 'Ремёсла', 'en' => 'Crafts', 'ro' => 'Meșteșuguri'],
            'summary' => ['ru' => 'Детская мебель из дерева по методике Эмми Пиклер.', 'en' => 'Wooden children\'s furniture made by the Emmi Pikler method.', 'ro' => 'Mobilier din lemn pentru copii, după metoda Emmi Pikler.'],
        ],
        [
            'photo' => 'participants/participant-molochenko-elena.jpg',
            'name' => ['ru' => 'Елена Молоченко', 'en' => 'Elena Molochenko', 'ro' => 'Elena Molochenko'],
            'tag' => ['ru' => 'Сельское хозяйство', 'en' => 'Agriculture', 'ro' => 'Agricultură'],
            'summary' => ['ru' => '«Ecofood» — экологически чистые корма для сельхозживотных.', 'en' => '"Ecofood" — eco-friendly feed for farm animals.', 'ro' => '„Ecofood” — furaje ecologice pentru animale de fermă.'],
        ],
        [
            'photo' => 'participants/participant-moroz-violetta.jpg',
            'name' => ['ru' => 'Виолетта Мороз', 'en' => 'Violetta Moroz', 'ro' => 'Violetta Moroz'],
            'tag' => ['ru' => 'Общепит', 'en' => 'Food service', 'ro' => 'Alimentație'],
            'summary' => ['ru' => 'Пельмени, вареники и равиоли ручной лепки.', 'en' => 'Handmade dumplings, vareniki and ravioli.', 'ro' => 'Colțunași, vareniki și ravioli făcute manual.'],
        ],
        [
            'photo' => null,
            'name' => ['ru' => 'Людмила Палий', 'en' => 'Lyudmila Paliy', 'ro' => 'Lyudmila Paliy'],
            'tag' => ['ru' => 'Кондитерское дело', 'en' => 'Confectionery', 'ro' => 'Cofetărie'],
            'summary' => ['ru' => '«Вкусный выбор» — домашняя выпечка и плацинды на заказ.', 'en' => '"Tasty Choice" — homemade pastries and plăcinte to order.', 'ro' => '„Alegerea Gustoasă” — patiserie de casă și plăcinte la comandă.'],
        ],
        [
            'photo' => 'participants/participant-rotaru-valentina.jpg',
            'name' => ['ru' => 'Валентина Ротару', 'en' => 'Valentina Rotaru', 'ro' => 'Valentina Rotaru'],
            'tag' => ['ru' => 'Ремёсла', 'en' => 'Crafts', 'ro' => 'Meșteșuguri'],
            'summary' => ['ru' => 'EcologicStup — деревянные ульи с молдавскими мотивами на солнечной энергии.', 'en' => '"EcologicStup" — solar-powered wooden hives with Moldovan folk motifs.', 'ro' => '„EcologicStup” — stupi din lemn cu motive populare, pe energie solară.'],
        ],
        [
            'photo' => 'participants/participant-stasyukova-nina.jpg',
            'name' => ['ru' => 'Нина Стасюкова', 'en' => 'Nina Stasyukova', 'ro' => 'Nina Stasyukova'],
            'tag' => ['ru' => 'Ремёсла', 'en' => 'Crafts', 'ro' => 'Meșteșuguri'],
            'summary' => ['ru' => 'Керамическая мастерская — художественные изделия и обжиг на солнечной энергии.', 'en' => 'A ceramics studio — art pieces fired using solar power.', 'ro' => 'Un atelier de ceramică — piese de artă arse cu energie solară.'],
        ],
        [
            'photo' => 'participants/participant-toporova-diana.jpg',
            'name' => ['ru' => 'Диана Топорова', 'en' => 'Diana Toporova', 'ro' => 'Diana Toporova'],
            'tag' => ['ru' => 'Образование', 'en' => 'Education', 'ro' => 'Educație'],
            'summary' => ['ru' => 'Академия программирования для детей и подростков «Impact A&C».', 'en' => '"Impact A&C" — a coding academy for children and teens.', 'ro' => '„Impact A&C” — o academie de programare pentru copii și adolescenți.'],
        ],
        [
            'photo' => 'participants/participant-hamuraru-olesya.jpg',
            'name' => ['ru' => 'Олеся Хамурару', 'en' => 'Olesya Hamuraru', 'ro' => 'Olesya Hamuraru'],
            'tag' => ['ru' => 'Ремёсла', 'en' => 'Crafts', 'ro' => 'Meșteșuguri'],
            'summary' => ['ru' => 'Top Candles — свечи из натурального воска с живыми цветами.', 'en' => '"Top Candles" — natural wax candles with real flowers.', 'ro' => '„Top Candles” — lumânări din ceară naturală cu flori adevărate.'],
        ],
        [
            'photo' => 'participants/participant-dronik-elena.jpg',
            'name' => ['ru' => 'Елена Дроник', 'en' => 'Elena Dronik', 'ro' => 'Elena Dronik'],
            'tag' => ['ru' => 'Флористика', 'en' => 'Florals', 'ro' => 'Floristică'],
            'summary' => ['ru' => 'House of Flowers — экологичные сухоцветы для флористики.', 'en' => '"House of Flowers" — eco-friendly dried flowers for florists.', 'ro' => '„House of Flowers” — flori uscate ecologice pentru floristică.'],
        ],
        [
            'photo' => null,
            'name' => ['ru' => 'Виктория Орликовская', 'en' => 'Viktoriya Orlikovskaya', 'ro' => 'Viktoriya Orlikovskaya'],
            'tag' => ['ru' => 'Общепит', 'en' => 'Food service', 'ro' => 'Alimentație'],
            'summary' => ['ru' => 'Ресторан «Прага» — банкеты и проект энергоэффективного HoReCa.', 'en' => '"Praha" restaurant — banquets and an energy-efficiency project.', 'ro' => 'Restaurantul „Praha” — banchete și un proiect de eficiență energetică.'],
        ],
        [
            'photo' => 'participants/participant-artyuhova-viktoriya.jpg',
            'name' => ['ru' => 'Виктория Артюхова', 'en' => 'Viktoriya Artyuhova', 'ro' => 'Viktoriya Artyuhova'],
            'tag' => ['ru' => 'Сельское хозяйство', 'en' => 'Agriculture', 'ro' => 'Agricultură'],
            'summary' => ['ru' => 'Микрозелень и пищевые цветы без химикатов и пестицидов.', 'en' => 'Microgreens and edible flowers, grown without chemicals.', 'ro' => 'Microverdețuri și flori comestibile, cultivate fără chimicale.'],
        ],
        [
            'photo' => 'participants/participant-artyuhova-olesya.jpg',
            'name' => ['ru' => 'Олеся Артюхова', 'en' => 'Olesya Artyuhova', 'ro' => 'Olesya Artyuhova'],
            'tag' => ['ru' => 'Сельское хозяйство', 'en' => 'Agriculture', 'ro' => 'Agricultură'],
            'summary' => ['ru' => 'Автоматизированные теплицы — экологически чистые овощи.', 'en' => 'Automated greenhouses growing eco-friendly vegetables.', 'ro' => 'Sere automatizate cu legume ecologice.'],
        ],
        [
            'photo' => 'participants/participant-afonina-violetta.jpg',
            'name' => ['ru' => 'Виолетта Афонина', 'en' => 'Violetta Afonina', 'ro' => 'Violetta Afonina'],
            'tag' => ['ru' => 'Сельское хозяйство', 'en' => 'Agriculture', 'ro' => 'Agricultură'],
            'summary' => ['ru' => 'Устойчивое хозяйство — овощи, фрукты, цветы и рассада.', 'en' => 'A sustainable farm growing vegetables, fruit, flowers and seedlings.', 'ro' => 'O fermă durabilă cu legume, fructe, flori și răsaduri.'],
        ],
        [
            'photo' => 'participants/participant-bugaeva-yuliya.jpg',
            'name' => ['ru' => 'Юлия Бугаева', 'en' => 'Yuliya Bugaeva', 'ro' => 'Yuliya Bugaeva'],
            'tag' => ['ru' => 'Сельское хозяйство', 'en' => 'Agriculture', 'ro' => 'Agricultură'],
            'summary' => ['ru' => 'Семейный виноградник с экологически ориентированным производством.', 'en' => 'A family vineyard with eco-oriented production.', 'ro' => 'O vie de familie cu producție orientată ecologic.'],
        ],
        [
            'photo' => 'participants/participant-vikol-tatyana.jpg',
            'name' => ['ru' => 'Татьяна Викол', 'en' => 'Tatyana Vikol', 'ro' => 'Tatyana Vikol'],
            'tag' => ['ru' => 'Животноводство', 'en' => 'Livestock', 'ro' => 'Zootehnie'],
            'summary' => ['ru' => 'Разведение кроликов — экологически чистое диетическое мясо.', 'en' => 'Rabbit farming — eco-friendly, dietetic meat.', 'ro' => 'Creșterea iepurilor — carne dietetică, ecologică.'],
        ],
        [
            'photo' => 'participants/participant-gorgan-elena.jpg',
            'name' => ['ru' => 'Елена Горган', 'en' => 'Elena Gorgan', 'ro' => 'Elena Gorgan'],
            'tag' => ['ru' => 'Пчеловодство', 'en' => 'Beekeeping', 'ro' => 'Apicultură'],
            'summary' => ['ru' => 'Пасека на Днестровской равнине — мёд и продукты пчеловодства.', 'en' => 'An apiary on the Dniester plain — honey and bee products.', 'ro' => 'O stupină pe câmpia Nistrului — miere și produse apicole.'],
        ],
        [
            'photo' => 'participants/participant-grishchenko-oksana.jpg',
            'name' => ['ru' => 'Оксана Грищенко', 'en' => 'Oksana Grishchenko', 'ro' => 'Oksana Grishchenko'],
            'tag' => ['ru' => 'Животноводство', 'en' => 'Livestock', 'ro' => 'Zootehnie'],
            'summary' => ['ru' => 'Семейное хозяйство — куры и поросята голландской породы.', 'en' => 'A family farm raising chickens and Dutch-breed piglets.', 'ro' => 'O fermă de familie cu găini și purcei de rasă olandeză.'],
        ],
        [
            'photo' => 'participants/participant-istomina-mariya.jpg',
            'name' => ['ru' => 'Мария Истомина', 'en' => 'Mariya Istomina', 'ro' => 'Mariya Istomina'],
            'tag' => ['ru' => 'Сельское хозяйство', 'en' => 'Agriculture', 'ro' => 'Agricultură'],
            'summary' => ['ru' => 'Тепличная зелень и овощи на дождевой воде и компосте.', 'en' => 'Greenhouse greens and vegetables, grown with rainwater and compost.', 'ro' => 'Verdețuri și legume de seră, cu apă de ploaie și compost.'],
        ],
        [
            'photo' => 'participants/participant-kerner-svetlana.jpg',
            'name' => ['ru' => 'Светлана Кернер', 'en' => 'Svetlana Kerner', 'ro' => 'Svetlana Kerner'],
            'tag' => ['ru' => 'Животноводство', 'en' => 'Livestock', 'ro' => 'Zootehnie'],
            'summary' => ['ru' => 'Фермерство — свиньи и куры на собственном корме.', 'en' => 'Farming pigs and chickens on home-grown feed.', 'ro' => 'Creșterea porcilor și găinilor cu furaje proprii.'],
        ],
        [
            'photo' => 'participants/participant-klimenko-stella.jpg',
            'name' => ['ru' => 'Стелла Клименко', 'en' => 'Stella Klimenko', 'ro' => 'Stella Klimenko'],
            'tag' => ['ru' => 'Текстиль', 'en' => 'Textiles', 'ro' => 'Textile'],
            'summary' => ['ru' => 'Безотходное шитьё — экосумки и одежда из текстильных остатков.', 'en' => 'Zero-waste sewing — eco-bags and clothing from textile scraps.', 'ro' => 'Croitorie fără deșeuri — genți eco și haine din resturi textile.'],
        ],
        [
            'photo' => 'participants/participant-marchenko-olesya.jpg',
            'name' => ['ru' => 'Олеся Марченко', 'en' => 'Olesya Marchenko', 'ro' => 'Olesya Marchenko'],
            'tag' => ['ru' => 'Флористика', 'en' => 'Florals', 'ro' => 'Floristică'],
            'summary' => ['ru' => 'Тепличные цветы и зелень круглый год.', 'en' => 'Greenhouse flowers and greens, year-round.', 'ro' => 'Flori și verdețuri de seră, tot anul.'],
        ],
        [
            'photo' => null,
            'name' => ['ru' => 'Ирина Новицкая', 'en' => 'Irina Novitskaya', 'ro' => 'Irina Novitskaya'],
            'tag' => ['ru' => 'Сельское хозяйство', 'en' => 'Agriculture', 'ro' => 'Agricultură'],
            'summary' => ['ru' => 'Восстановление сада — плодовые деревья и ягодные культуры.', 'en' => 'Orchard restoration — fruit trees and berry crops.', 'ro' => 'Refacerea unei livezi — pomi fructiferi și arbuști fructiferi.'],
        ],
        [
            'photo' => 'participants/participant-oleynik-lyudmila.jpg',
            'name' => ['ru' => 'Людмила Олейник', 'en' => 'Lyudmila Oleynik', 'ro' => 'Lyudmila Oleynik'],
            'tag' => ['ru' => 'Сельское хозяйство', 'en' => 'Agriculture', 'ro' => 'Agricultură'],
            'summary' => ['ru' => 'Экохозяйство на солнечной энергии — 12 панелей для теплиц.', 'en' => 'A solar-powered eco-farm — 12 panels powering the greenhouses.', 'ro' => 'O fermă ecologică pe energie solară — 12 panouri pentru sere.'],
        ],
        [
            'photo' => 'participants/participant-puga-viktoriya.jpg',
            'name' => ['ru' => 'Виктория Пуга', 'en' => 'Viktoriya Puga', 'ro' => 'Viktoriya Puga'],
            'tag' => ['ru' => 'Пчеловодство', 'en' => 'Beekeeping', 'ro' => 'Apicultură'],
            'summary' => ['ru' => 'Пасека — от 5 до 30 ульев, мёд и свечи ручной работы.', 'en' => 'An apiary grown from 5 to 30 hives — honey and handmade candles.', 'ro' => 'O stupină extinsă de la 5 la 30 de familii — miere și lumânări făcute manual.'],
        ],
        [
            'photo' => null,
            'name' => ['ru' => 'Татьяна Сергеева', 'en' => 'Tatyana Sergeeva', 'ro' => 'Tatyana Sergeeva'],
            'tag' => ['ru' => 'Сельское хозяйство', 'en' => 'Agriculture', 'ro' => 'Agricultură'],
            'summary' => ['ru' => 'Домашнее хозяйство — устойчивые практики и сбор дождевой воды.', 'en' => 'A household farm using sustainable, rainwater-harvesting practices.', 'ro' => 'O gospodărie cu practici durabile și colectarea apei de ploaie.'],
        ],
        [
            'photo' => 'participants/participant-speyan-valentina.jpg',
            'name' => ['ru' => 'Валентина Спеян', 'en' => 'Valentina Speyan', 'ro' => 'Valentina Speyan'],
            'tag' => ['ru' => 'Пчеловодство', 'en' => 'Beekeeping', 'ro' => 'Apicultură'],
            'summary' => ['ru' => 'Пасека — от 4 пчелиных семей до солнечного оборудования.', 'en' => 'An apiary that grew from 4 hives to solar-powered equipment.', 'ro' => 'O stupină crescută de la 4 familii, cu echipament pe energie solară.'],
        ],
        [
            'photo' => 'participants/participant-stolyarova-lyudmila.jpg',
            'name' => ['ru' => 'Людмила Столярова', 'en' => 'Lyudmila Stolyarova', 'ro' => 'Lyudmila Stolyarova'],
            'tag' => ['ru' => 'Пчеловодство', 'en' => 'Beekeeping', 'ro' => 'Apicultură'],
            'summary' => ['ru' => 'Пасека агронома — мёд и сохранение биоразнообразия.', 'en' => 'An agronomist\'s apiary — honey and biodiversity conservation.', 'ro' => 'Stupina unui agronom — miere și conservarea biodiversității.'],
        ],
        [
            'photo' => 'participants/participant-tataru-liliya.jpg',
            'name' => ['ru' => 'Лилия Татару', 'en' => 'Liliya Tataru', 'ro' => 'Liliya Tataru'],
            'tag' => ['ru' => 'Пчеловодство', 'en' => 'Beekeeping', 'ro' => 'Apicultură'],
            'summary' => ['ru' => 'Пасека у Днестра — мёд и сельский туризм с апитерапией.', 'en' => 'An apiary by the Dniester — honey and apitherapy-based rural tourism.', 'ro' => 'O stupină lângă Nistru — miere și turism rural cu apiterapie.'],
        ],
        [
            'photo' => 'participants/participant-filipenko-tatyana.jpg',
            'name' => ['ru' => 'Татьяна Филипенко', 'en' => 'Tatyana Filipenko', 'ro' => 'Tatyana Filipenko'],
            'tag' => ['ru' => 'Животноводство', 'en' => 'Livestock', 'ro' => 'Zootehnie'],
            'summary' => ['ru' => 'Мини-ферма кролиководства — семейное дело и доход на пенсии.', 'en' => 'A small rabbit farm — a family business and retirement income.', 'ro' => 'O mini-fermă de iepuri — afacere de familie și venit la pensie.'],
        ],
        [
            'photo' => 'participants/participant-harchenko-olga.jpg',
            'name' => ['ru' => 'Ольга Харченко', 'en' => 'Olga Harchenko', 'ro' => 'Olga Harchenko'],
            'tag' => ['ru' => 'Сыроварение', 'en' => 'Cheesemaking', 'ro' => 'Fabricarea brânzei'],
            'summary' => ['ru' => 'Домашнее производство сыра — безотходный процесс без химии.', 'en' => 'Home cheesemaking — a zero-waste process without chemicals.', 'ro' => 'Producție de brânză de casă — proces fără deșeuri și fără chimicale.'],
        ],
        [
            'photo' => 'participants/participant-shelenkova-lyudmila.jpg',
            'name' => ['ru' => 'Людмила Шеленкова', 'en' => 'Lyudmila Shelenkova', 'ro' => 'Lyudmila Shelenkova'],
            'tag' => ['ru' => 'Сыроварение', 'en' => 'Cheesemaking', 'ro' => 'Fabricarea brânzei'],
            'summary' => ['ru' => 'Крафтовые сыры из козьего молока экологичных коз.', 'en' => 'Craft cheese made from the milk of eco-raised goats.', 'ro' => 'Brânză artizanală din laptele caprelor crescute ecologic.'],
        ],
        [
            'photo' => 'participants/participant-guzun-galina.jpg',
            'name' => ['ru' => 'Галина Гузун', 'en' => 'Galina Guzun', 'ro' => 'Galina Guzun'],
            'tag' => ['ru' => 'Красота', 'en' => 'Beauty', 'ro' => 'Frumusețe'],
            'summary' => ['ru' => 'Салон «Пион» — парикмахерские услуги и уход за волосами.', 'en' => '"Pion" salon — hairdressing and hair care.', 'ro' => 'Salonul „Pion” — servicii de coafură și îngrijire a părului.'],
        ],
        [
            'photo' => 'participants/participant-melenteva-natalya.jpg',
            'name' => ['ru' => 'Наталья Мелентьева', 'en' => 'Natalya Melenteva', 'ro' => 'Natalya Melenteva'],
            'tag' => ['ru' => 'Ремёсла', 'en' => 'Crafts', 'ro' => 'Meșteșuguri'],
            'summary' => ['ru' => 'Изделия из бетона и гипса — декор и мастер-классы.', 'en' => 'Concrete and plaster decor pieces, plus hands-on workshops.', 'ro' => 'Obiecte decorative din beton și ipsos, plus ateliere practice.'],
        ],
        [
            'photo' => 'participants/participant-nikolaeva-olga.jpg',
            'name' => ['ru' => 'Ольга Николаева', 'en' => 'Olga Nikolaeva', 'ro' => 'Olga Nikolaeva'],
            'tag' => ['ru' => 'Мода', 'en' => 'Fashion', 'ro' => 'Modă'],
            'summary' => ['ru' => 'Свадебные платья премиум-класса, теперь и детские нарядные наряды.', 'en' => 'Premium wedding gowns, now also formal children\'s wear.', 'ro' => 'Rochii de mireasă premium, acum și ținute elegante pentru copii.'],
        ],
        [
            'photo' => 'participants/participant-robytnik-irina.jpg',
            'name' => ['ru' => 'Ирина Робытник', 'en' => 'Irina Robytnik', 'ro' => 'Irina Robytnik'],
            'tag' => ['ru' => 'Дизайн', 'en' => 'Design', 'ro' => 'Design'],
            'summary' => ['ru' => 'Дизайн интерьера, экстерьера и ландшафта — более 30 проектов.', 'en' => 'Interior, exterior and landscape design — over 30 projects.', 'ro' => 'Design interior, exterior și peisagistic — peste 30 de proiecte.'],
        ],
        [
            'photo' => 'participants/participant-chernichenko-viktoriya.jpg',
            'name' => ['ru' => 'Виктория Черниченко', 'en' => 'Viktoriya Chernichenko', 'ro' => 'Viktoriya Chernichenko'],
            'tag' => ['ru' => 'Общепит', 'en' => 'Food service', 'ro' => 'Alimentație'],
            'summary' => ['ru' => 'Семейная пекарня — хлеб холодной ферментации, булочки, пирожки.', 'en' => 'A family bakery — cold-fermented bread, buns and pastries.', 'ro' => 'O brutărie de familie — pâine cu fermentare la rece, chifle, plăcinte.'],
        ],
        [
            'photo' => 'participants/participant-svetlana-bulavkina.jpg',
            'name' => ['ru' => 'Светлана Булавкина', 'en' => 'Svetlana Bulavkina', 'ro' => 'Svetlana Bulavkina'],
            'tag' => ['ru' => 'Фотография', 'en' => 'Photography', 'ro' => 'Fotografie'],
            'summary' => ['ru' => 'Ньюборн-фотосессии — первый такой формат на местном рынке.', 'en' => 'Newborn photo sessions — the first of their kind locally.', 'ro' => 'Ședințe foto newborn — primele de acest fel pe piața locală.'],
        ],
        [
            'photo' => 'participants/participant-reveka-natalya.jpg',
            'name' => ['ru' => 'Наталья Ревека', 'en' => 'Natalya Reveka', 'ro' => 'Natalya Reveka'],
            'tag' => ['ru' => 'Мода', 'en' => 'Fashion', 'ro' => 'Modă'],
            'summary' => ['ru' => 'Ателье пошива и ремонта одежды — индивидуальный подход.', 'en' => 'A sewing and repair atelier with a personal approach.', 'ro' => 'Un atelier de croitorie și reparații, cu abordare personalizată.'],
        ],
        [
            'photo' => 'participants/participant-tsurkan-dorunga-ekaterina.jpg',
            'name' => ['ru' => 'Екатерина Цуркан-Дорунга', 'en' => 'Ekaterina Tsurkan-Dorunga', 'ro' => 'Ekaterina Tsurkan-Dorunga'],
            'tag' => ['ru' => 'Психология', 'en' => 'Psychology', 'ro' => 'Psihologie'],
            'summary' => ['ru' => '«Шаги к себе» — тренинги по психологии и личностному росту.', 'en' => '"Steps to Yourself" — psychology and personal-growth training.', 'ro' => '„Pași spre Tine” — traininguri de psihologie și dezvoltare personală.'],
        ],
        [
            'photo' => 'participants/participant-gaypel-elena.jpg',
            'name' => ['ru' => 'Елена Гайпель', 'en' => 'Elena Gaypel', 'ro' => 'Elena Gaypel'],
            'tag' => ['ru' => 'Столярное дело', 'en' => 'Woodworking', 'ro' => 'Tâmplărie'],
            'summary' => ['ru' => 'Столярная мастерская — изделия из дерева и смолы.', 'en' => 'A woodworking studio — pieces made of wood and resin.', 'ro' => 'Un atelier de tâmplărie — obiecte din lemn și rășină.'],
        ],
        [
            'photo' => 'participants/participant-kalashnikova-ekaterina.jpg',
            'name' => ['ru' => 'Екатерина Калашникова', 'en' => 'Ekaterina Kalashnikova', 'ro' => 'Ekaterina Kalashnikova'],
            'tag' => ['ru' => 'Туризм', 'en' => 'Tourism', 'ro' => 'Turism'],
            'summary' => ['ru' => 'Прокат квадроциклов — первый такой проект в Приднестровье.', 'en' => 'ATV rental — the first of its kind in the region.', 'ro' => 'Închiriere de ATV-uri — primul proiect de acest fel din regiune.'],
        ],
        [
            'photo' => 'participants/participant-tihonyuk-yuliya.jpg',
            'name' => ['ru' => 'Юлия Тихонюк', 'en' => 'Yuliya Tihonyuk', 'ro' => 'Yuliya Tihonyuk'],
            'tag' => ['ru' => 'Образование', 'en' => 'Education', 'ro' => 'Educație'],
            'summary' => ['ru' => 'Эко-студия для дошкольников — рисование и поделки из вторсырья.', 'en' => 'An eco-studio for preschoolers — art and crafts from recycled materials.', 'ro' => 'Un eco-studio pentru preșcolari — desen și meșteșuguri din materiale reciclate.'],
        ],
        [
            'photo' => 'participants/participant-ermuraki-irina.jpg',
            'name' => ['ru' => 'Ирина Ермураки', 'en' => 'Irina Ermuraki', 'ro' => 'Irina Ermuraki'],
            'tag' => ['ru' => 'Клининг', 'en' => 'Cleaning', 'ro' => 'Curățenie'],
            'summary' => ['ru' => 'Клининговая компания — уборка квартир, домов и офисов.', 'en' => 'A cleaning company for homes, houses and offices.', 'ro' => 'O companie de curățenie pentru case și birouri.'],
        ],
        [
            'photo' => 'participants/participant-topalskaya-arina.jpg',
            'name' => ['ru' => 'Арина Топальская', 'en' => 'Arina Topalskaya', 'ro' => 'Arina Topalskaya'],
            'tag' => ['ru' => 'Фотография', 'en' => 'Photography', 'ro' => 'Fotografie'],
            'summary' => ['ru' => 'Фото-контент для брендов, магазинов, ресторанов и салонов.', 'en' => 'Photo content for brands, shops, restaurants and salons.', 'ro' => 'Conținut foto pentru branduri, magazine, restaurante și saloane.'],
        ],
        [
            'photo' => 'participants/participant-shirokova-aleksandra.jpg',
            'name' => ['ru' => 'Александра Широкова', 'en' => 'Aleksandra Shirokova', 'ro' => 'Aleksandra Shirokova'],
            'tag' => ['ru' => 'Событийный бизнес', 'en' => 'Events', 'ro' => 'Evenimente'],
            'summary' => ['ru' => 'Аэродизайн — оформление праздников гелиевыми и биоразлагаемыми шарами.', 'en' => 'Balloon decor for events, using biodegradable balloons.', 'ro' => 'Decor cu baloane pentru evenimente, cu baloane biodegradabile.'],
        ],
        [
            'photo' => 'participants/participant-kulichenko-mariya.jpg',
            'name' => ['ru' => 'Мария Куличенко', 'en' => 'Mariya Kulichenko', 'ro' => 'Mariya Kulichenko'],
            'tag' => ['ru' => 'Ремёсла', 'en' => 'Crafts', 'ro' => 'Meșteșuguri'],
            'summary' => ['ru' => 'Деревянные сувениры по мотивам «Гарри Поттера» — семейное дело.', 'en' => '"Harry Potter"-inspired wooden souvenirs — a family business.', 'ro' => 'Suveniruri din lemn inspirate din „Harry Potter” — afacere de familie.'],
        ],
        [
            'photo' => 'participants/participant-chernobrivchenko-yuliya.jpg',
            'name' => ['ru' => 'Юлия Чернобривченко', 'en' => 'Yuliya Chernobrivchenko', 'ro' => 'Yuliya Chernobrivchenko'],
            'tag' => ['ru' => 'Текстиль', 'en' => 'Textiles', 'ro' => 'Textile'],
            'summary' => ['ru' => 'Одежда с индивидуальной компьютерной вышивкой.', 'en' => 'Clothing with personalised computerised embroidery.', 'ro' => 'Haine cu broderie computerizată personalizată.'],
        ],
        [
            'photo' => 'participants/participant-dontsova-irina.jpg',
            'name' => ['ru' => 'Ирина Донцова', 'en' => 'Irina Dontsova', 'ro' => 'Irina Dontsova'],
            'tag' => ['ru' => 'Мода', 'en' => 'Fashion', 'ro' => 'Modă'],
            'summary' => ['ru' => 'Iren\'s Secret — бренд одежды и спецодежда для разных отраслей.', 'en' => '"Iren\'s Secret" — a clothing brand, now also workwear.', 'ro' => '„Iren\'s Secret” — un brand de haine, acum și echipament de lucru.'],
        ],
        [
            'photo' => 'participants/participant-sandetskaya-tatyana.jpg',
            'name' => ['ru' => 'Татьяна Сандецкая', 'en' => 'Tatyana Sandetskaya', 'ro' => 'Tatyana Sandetskaya'],
            'tag' => ['ru' => 'Психология', 'en' => 'Psychology', 'ro' => 'Psihologie'],
            'summary' => ['ru' => '«Нейрофитнес» — нейротестирование, нейрокоррекция и психотерапия.', 'en' => '"Neurofitness" — neuro-testing, neuro-correction and psychotherapy.', 'ro' => '„Neurofitness” — neurotestare, neurocorecție și psihoterapie.'],
        ],
        [
            'photo' => 'participants/participant-shalaeva-olga.jpg',
            'name' => ['ru' => 'Ольга Шалаева', 'en' => 'Olga Shalaeva', 'ro' => 'Olga Shalaeva'],
            'tag' => ['ru' => 'Производство', 'en' => 'Manufacturing', 'ro' => 'Producție'],
            'summary' => ['ru' => 'Шелкография — трудоустройство инвалидов по слуху и речи.', 'en' => 'A screen-printing workshop employing deaf and speech-impaired workers.', 'ro' => 'Un atelier de serigrafie care angajează persoane cu deficiențe de auz și vorbire.'],
        ],
        [
            'photo' => 'participants/participant-shirokova-elizaveta.jpg',
            'name' => ['ru' => 'Елизавета Широкова', 'en' => 'Elizaveta Shirokova', 'ro' => 'Elizaveta Shirokova'],
            'tag' => ['ru' => 'Животноводство', 'en' => 'Livestock', 'ro' => 'Zootehnie'],
            'summary' => ['ru' => '«Кудесы Деликатесы» — ферма индейки, деликатесное мясо.', 'en' => '"Kudesy Delikatesy" — a turkey farm producing gourmet meat.', 'ro' => '„Kudesy Delikatesy” — o fermă de curcani, carne delicatesă.'],
        ],
        [
            'photo' => 'participants/participant-davidchuk-nelya.jpg',
            'name' => ['ru' => 'Неля Давидчук', 'en' => 'Nelya Davidchuk', 'ro' => 'Nelya Davidchuk'],
            'tag' => ['ru' => 'Общепит', 'en' => 'Food service', 'ro' => 'Alimentație'],
            'summary' => ['ru' => 'Павильон сладкой ваты, попкорна и детского проката в Первомайске.', 'en' => 'A cotton candy, popcorn and kids\' rides kiosk in Pervomaisk.', 'ro' => 'Un chioșc cu vată de zahăr, popcorn și distracții pentru copii, în Pervomaisk.'],
        ],
        [
            'photo' => 'participants/participant-kalashnik-aleksandra.jpg',
            'name' => ['ru' => 'Александра Калашник', 'en' => 'Aleksandra Kalashnik', 'ro' => 'Aleksandra Kalashnik'],
            'tag' => ['ru' => 'Событийный бизнес', 'en' => 'Events', 'ro' => 'Evenimente'],
            'summary' => ['ru' => 'Кинотеатр под открытым небом на виноградниках.', 'en' => 'An open-air cinema among the vineyards.', 'ro' => 'Un cinema în aer liber, printre vii.'],
        ],
        [
            'photo' => null,
            'name' => ['ru' => 'Татьяна Кузьминецкая', 'en' => 'Tatyana Kuzminetskaya', 'ro' => 'Tatyana Kuzminetskaya'],
            'tag' => ['ru' => 'Маркетинг', 'en' => 'Marketing', 'ro' => 'Marketing'],
            'summary' => ['ru' => 'SMM-агентство Ultra — продвижение брендов в соцсетях.', 'en' => '"Ultra" — an SMM agency for social media brand promotion.', 'ro' => '„Ultra” — o agenție SMM pentru promovarea brandurilor pe rețele sociale.'],
        ],
        [
            'photo' => 'participants/participant-maslova-nataliya.jpg',
            'name' => ['ru' => 'Наталия Маслова', 'en' => 'Nataliya Maslova', 'ro' => 'Nataliya Maslova'],
            'tag' => ['ru' => 'Здоровье и фитнес', 'en' => 'Health & fitness', 'ro' => 'Sănătate și fitness'],
            'summary' => ['ru' => 'Первая в Тирасполе сайкл-студия — групповые тренировки.', 'en' => 'Tiraspol\'s first cycle studio — group workouts.', 'ro' => 'Primul studio de cycling din Tiraspol — antrenamente de grup.'],
        ],
        [
            'photo' => 'participants/participant-osipenko.jpg',
            'name' => ['ru' => 'Ирина Осипенко', 'en' => 'Irina Osipenko', 'ro' => 'Irina Osipenko'],
            'tag' => ['ru' => 'Красота', 'en' => 'Beauty', 'ro' => 'Frumusețe'],
            'summary' => ['ru' => 'Кабинет лазерной эпиляции.', 'en' => 'A laser hair removal studio.', 'ro' => 'Un cabinet de epilare laser.'],
        ],
        [
            'photo' => 'participants/participant-saveleva-elena.jpg',
            'name' => ['ru' => 'Елена Савельева', 'en' => 'Elena Saveleva', 'ro' => 'Elena Saveleva'],
            'tag' => ['ru' => 'Образование', 'en' => 'Education', 'ro' => 'Educație'],
            'summary' => ['ru' => 'Студия творческого развития для детей 2–16 лет.', 'en' => 'A creative development studio for children aged 2 to 16.', 'ro' => 'Un studio de dezvoltare creativă pentru copii de 2-16 ani.'],
        ],
        [
            'photo' => 'participants/participant-uhalskaya-olga.jpg',
            'name' => ['ru' => 'Ольга Ухальская', 'en' => 'Olga Uhalskaya', 'ro' => 'Olga Uhalskaya'],
            'tag' => ['ru' => 'Туризм', 'en' => 'Tourism', 'ro' => 'Turism'],
            'summary' => ['ru' => 'Туристическое агентство — индивидуальные туры по Молдове и Приднестровью.', 'en' => 'A travel agency — custom tours through Moldova and Transnistria.', 'ro' => 'O agenție de turism — tururi personalizate în Moldova și Transnistria.'],
        ],
    ];

    $locales = ['ru', 'en', 'ro'];

    // The full roster is expected to grow toward ~500 people, so only the first page is
    // rendered server-side; the rest ships as JSON and is appended client-side in batches
    // by public/themes/public/miro/js/members.js when "Показать ещё" is clicked.
    $participantsPageSize = 24;
    $participantsVisible = array_slice($participants, 0, $participantsPageSize);
    $participantsRemaining = array_slice($participants, $participantsPageSize);
    $participantsTotal = count($participants);
@endphp

<!DOCTYPE html>
<html lang="ru" class="miro-page scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women Entrepreneurs Platform — Participants</title>
    <link rel="icon" type="image/png" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/brand/favicon.png') }}">
    <meta name="description" content="Public directory of participants of Women Entrepreneurs Platform.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600&family=Prata&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/css/members.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/css/navigation.css') }}">
</head>
<body>
    @include('themes.public.miro.partials.miro-header', ['miroCurrentPage' => 'members'])

    <main class="miro-members-page">

        <section class="miro-members-section">
            <div class="miro-container">
                <div class="miro-section__head">
                    <h2><span data-lang="ru">Участницы платформы</span><span data-lang="en">Platform participants</span><span data-lang="ro">Participantele platformei</span></h2>
                    <p><span data-lang="ru">Предпринимательницы, которые уже развивают собственное дело при поддержке платформы — от пищевого производства до туризма и ремёсел.</span><span data-lang="en">Entrepreneurs already growing their businesses with the platform’s support — from food production to tourism and crafts.</span><span data-lang="ro">Antreprenoare care își dezvoltă deja afacerea cu sprijinul platformei — de la producție alimentară la turism și meșteșuguri.</span></p>
                </div>

                <div class="miro-participants-grid" id="miro-participants-grid" data-image-base="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images') }}/" data-page-size="{{ $participantsPageSize }}">
                    @foreach($participantsVisible as $participant)
                        <article class="miro-participant-card">
                            @if($participant['photo'])
                                <img class="miro-participant-card__avatar" src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/' . $participant['photo']) }}" alt="{{ $participant['name']['en'] }}" loading="lazy">
                            @else
                                @php
                                    $initialsSource = preg_split('/\s+/u', trim($participant['name']['ru']));
                                    $initials = mb_strtoupper(mb_substr($initialsSource[0] ?? '', 0, 1) . mb_substr($initialsSource[1] ?? '', 0, 1));
                                @endphp
                                <span class="miro-participant-card__avatar miro-participant-card__avatar--placeholder" aria-hidden="true">{{ $initials }}</span>
                            @endif
                            <div class="miro-participant-card__body">
                                <span class="miro-participant-card__tag">@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $participant['tag'][$locale] }}</span>@endforeach</span>
                                <h3>@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $participant['name'][$locale] }}</span>@endforeach</h3>
                                <p>@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $participant['summary'][$locale] }}</span>@endforeach</p>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if(count($participantsRemaining) > 0)
                    <p class="miro-participants-count" id="miro-participants-count">
                        <span data-lang="ru" data-template="Показано {shown} из {total}">Показано {{ count($participantsVisible) }} из {{ $participantsTotal }}</span><span data-lang="en" data-template="Showing {shown} of {total}">Showing {{ count($participantsVisible) }} of {{ $participantsTotal }}</span><span data-lang="ro" data-template="Se afișează {shown} din {total}">Se afișează {{ count($participantsVisible) }} din {{ $participantsTotal }}</span>
                    </p>
                    <div class="miro-participants-loadmore">
                        <button type="button" id="miro-participants-loadmore-btn" class="miro-button miro-button--secondary">
                            <span data-lang="ru">Показать ещё</span><span data-lang="en">Show more</span><span data-lang="ro">Arată mai mult</span>
                        </button>
                    </div>
                    {{-- Consumed by members.js — kept out of the visible DOM so the browser
                         never requests these participants' photos until they're revealed. --}}
                    <script type="application/json" id="miro-participants-remaining">{!! json_encode($participantsRemaining, JSON_UNESCAPED_UNICODE) !!}</script>
                @endif

                <section class="miro-members-cta">
                    <h2><span data-lang="ru">Зарегистрируйтесь, чтобы связаться</span><span data-lang="en">Register to make the connection</span><span data-lang="ro">Înregistrează-te pentru a lua legătura</span></h2>
                    <p><span data-lang="ru">Создайте профиль на платформе, чтобы находить нужных людей и обращаться к ним напрямую.</span><span data-lang="en">Create your platform profile to find the right people and reach out directly.</span><span data-lang="ro">Creează-ți profilul pentru a găsi oamenii potriviți și a lua legătura direct.</span></p>
                    <div class="miro-members-cta__actions">
                        <a href="{{ route('account.login') }}" class="miro-button miro-button--pink"><span data-lang="ru">Войти в кабинет</span><span data-lang="en">Open the cabinet</span><span data-lang="ro">Intră în cabinet</span></a>
                        <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button" style="border:1px solid rgba(255,255,255,.35);color:#fff"><span data-lang="ru">Присоединиться через Telegram</span><span data-lang="en">Join via Telegram</span><span data-lang="ro">Alătură-te prin Telegram</span></a>
                    </div>
                </section>
            </div>
        </section>
    </main>

    @include('themes.public.miro.partials.miro-footer')
    <script defer src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/js/members.js') }}"></script>
</body>
</html>
