<?php

// Înregistrare, autentificare prin e-mail, formularul de profil și cererile pentru tarife. Originalul rus este în lang/ru.
return [

    'or' => 'sau',
    'back_to_site' => '← Înapoi la site',

    'google' => [
        'login' => 'Autentificare cu Google',
        'register' => 'Continuă cu Google',
        'failed' => 'Nu am putut face autentificarea cu Google. Încercați din nou sau intrați cu e-mailul.',
        'no_email' => 'Google nu a transmis o adresă de e-mail confirmată, așa că această metodă nu poate fi folosită.',
    ],

    'login' => [
        'title' => 'Autentificare',
        'heading' => 'Intrați în cont',
        'subtitle' => 'Contul participantei',
        'email' => 'E-mail',
        'password' => 'Parolă',
        'remember' => 'Ține-mă minte',
        'submit' => 'Intră',
        'forgot' => 'Ați uitat parola?',
        'no_account' => 'Nu aveți încă un cont?',
        'register_link' => 'Înregistrați-vă',
        'failed' => 'E-mail sau parolă incorectă.',
        'throttle' => 'Prea multe încercări de autentificare. Încercați peste :seconds s.',
        'access_closed' => 'Accesul la cont este închis. Dacă este o greșeală, scrieți-ne prin pagina „Contacte”.',
        'session_expired' => 'Sesiunea s-a încheiat. Autentificați-vă din nou.',
    ],

    'register' => [
        'title' => 'Înregistrare',
        'heading' => 'Creați un cont',
        'subtitle' => 'E-mail și parolă. Profilul și tariful le alegeți la pașii următori.',
        'email' => 'E-mail',
        'password' => 'Parolă',
        'password_hint' => 'Cel puțin 8 caractere',
        'password_confirmation' => 'Repetați parola',
        'consent' => 'Sunt de acord cu prelucrarea datelor cu caracter personal și accept',
        'consent_link' => 'regulile platformei',
        'submit' => 'Înregistrează-mă',
        'have_account' => 'Aveți deja un cont?',
        'login_link' => 'Intră',
    ],

    'verification' => [
        'title' => 'Confirmarea e-mailului',
        'heading' => 'Confirmați e-mailul',
        'text' => 'Am trimis un mesaj la :email. Deschideți-l și urmați linkul: după aceea puteți completa profilul.',
        'spam' => 'Nu a venit mesajul? Verificați folderul „Spam”.',
        'resend' => 'Trimite mesajul din nou',
        'resent' => 'Mesajul a fost trimis din nou.',
        'verified' => 'E-mailul a fost confirmat. Acum povestiți-ne despre dumneavoastră.',
        'invalid' => 'Linkul nu este valid sau a expirat. Cereți un mesaj nou.',
        'wrong_email' => 'Ați indicat un e-mail greșit?',
        'logout' => 'Ieșiți și începeți din nou',
        'sent' => 'Am trimis un mesaj cu linkul de confirmare.',
    ],

    'password' => [
        'forgot_title' => 'Recuperarea parolei',
        'forgot_heading' => 'Ați uitat parola?',
        'forgot_text' => 'Indicați e-mailul cu care este înregistrat contul și vă vom trimite un link pentru o parolă nouă.',
        'forgot_submit' => 'Trimite linkul',
        'forgot_sent' => 'Dacă acest e-mail este înregistrat, am trimis la el un mesaj cu linkul.',
        'reset_title' => 'Parolă nouă',
        'reset_heading' => 'Setați o parolă nouă',
        'reset_submit' => 'Salvează parola',
        'reset_done' => 'Parola a fost schimbată. Intrați cu parola nouă.',
        'reset_invalid' => 'Linkul pentru parolă nu este valid sau a expirat. Cereți unul nou.',
        'back_to_login' => 'Înapoi la autentificare',
    ],

    'onboarding' => [
        'title' => 'Profil',
        'step_email' => 'E-mail',
        'step_profile' => 'Profil',
        'step_plan' => 'Tarif',
        'heading' => 'Povestiți-ne despre dumneavoastră',
        'full_name' => 'Nume și prenume',
        'phone' => 'Telefon',
        'phone_hint' => 'De exemplu, +373 600 00 000',
        'description' => 'Despre dumneavoastră și activitatea dumneavoastră',
        'description_hint' => 'Cu ce vă ocupați, ce experiență sau ce afacere aveți.',
        'expectation' => 'Ce așteptați de la platformă',
        'expectation_hint' => 'Ce cunoștințe, oportunități și contacte vă trebuie.',
        'submit' => 'Continuă la tarife',
    ],

    'validation' => [
        'email_required' => 'Indicați e-mailul.',
        'email_invalid' => 'Verificați adresa de e-mail.',
        'email_taken' => 'Acest e-mail este deja înregistrat. Intrați sau recuperați parola.',
        'password_required' => 'Alegeți o parolă.',
        'password_min' => 'Parola trebuie să aibă cel puțin 8 caractere.',
        'password_confirmed' => 'Parolele nu coincid.',
        'consent' => 'Este necesar acordul pentru prelucrarea datelor și regulile platformei.',
        'full_name_required' => 'Indicați numele și prenumele.',
        'phone_required' => 'Indicați telefonul.',
        'phone_invalid' => 'Verificați telefonul: cifre, opțional cu „+”, spații și paranteze (de la 8 la 15 cifre).',
        'description_required' => 'Povestiți puțin despre dumneavoastră.',
        'expectation_required' => 'Scrieți ce așteptați de la platformă.',
    ],

    'plans' => [
        'choose_heading' => 'Alegeți un tarif',
        'choose_text' => 'Nu există acces gratuit: tariful deschide contul. Profilul este salvat, a mai rămas un ultim pas.',
        'buy' => 'Cumpără',
        'request' => 'Lasă o cerere',
        'request_hint' => 'Acest tarif este deschis deocamdată de manager. Lăsați o cerere: vă sună, explică condițiile și deschide plata.',
        'comment' => 'Comentariu pentru manager (opțional)',
        'request_sent' => 'Cererea a fost trimisă',
        'request_sent_text' => 'Un manager vă va suna la :phone și va deschide achiziția.',
        'unlocked' => 'Managerul v-a deschis achiziția acestui tarif.',
        'requested_flash' => 'Cererea a fost primită. Un manager vă va suna la :phone.',
        'not_closed' => 'Acest tarif poate fi cumpărat imediat, cererea nu este necesară.',
        'phone_missing' => 'Completați telefonul în profil: managerul vă va suna la el.',
    ],

    'telegram' => [
        'title' => 'Telegram pentru notificări',
        'text' => 'Conectați botul ca să primiți mesaje despre plată și sfârșitul tarifului, contactele potrivite și newslettere ale platformei. Nu vă puteți autentifica prin Telegram: intrarea se face doar prin e-mail.',
        'connect' => 'Conectează Telegram',
        'connected' => 'Telegram este conectat',
        'disconnect' => 'Deconectează',
        'disconnected' => 'Telegram a fost deconectat, notificările nu vor mai veni.',
    ],

    'banner' => [
        'pending' => 'Profilul dvs. este în curs de verificare. După aprobare, celelalte participante vă vor putea găsi.',
        'rejected' => 'Profilul a fost respins. Dacă este o greșeală, scrieți-ne prin pagina „Contacte”.',
    ],

    'contacts' => [
        'phone' => 'Telefon',
        'locked' => 'Contactele participantelor sunt deschise de la tariful Active.',
    ],

    'mail' => [
        'greeting' => 'Bună ziua!',
        'footer' => 'Acest mesaj a fost trimis automat, nu este nevoie să răspundeți.',
        'verify' => [
            'subject' => 'Confirmați e-mailul — Women’s hub',
            'line' => 'Vă înregistrați în contul Women’s hub. Apăsați butonul ca să confirmați e-mailul și să treceți la profil.',
            'button' => 'Confirmă e-mailul',
            'note' => 'Linkul este valabil 24 de ore. Dacă nu v-ați înregistrat, ignorați acest mesaj.',
        ],
        'reset' => [
            'subject' => 'Parolă nouă — Women’s hub',
            'line' => 'Ați cerut schimbarea parolei contului Women’s hub. Apăsați butonul ca să setați una nouă.',
            'button' => 'Setează parola nouă',
            'note' => 'Linkul este valabil 60 de minute. Dacă nu ați cerut nimic, parola rămâne aceeași, iar mesajul poate fi șters.',
        ],
        'subscription' => [
            'activated' => ['subject' => 'Tariful :plan este activ — Women’s hub', 'line' => 'Tariful :plan este activ până la :until. Vă mulțumim că sunteți cu noi!'],
            'reminder' => ['subject' => 'Tariful :plan se termină curând — Women’s hub', 'line' => 'Tariful :plan este valabil până la :until, au rămas :days zile. Prelungiți-l pe pagina „Abonament”, ca să nu pierdeți accesul.'],
            'expired' => ['subject' => 'Tariful :plan s-a încheiat — Women’s hub', 'line' => 'Tariful :plan s-a încheiat. Alegeți un tarif pe pagina „Abonament”, ca să recăpătați accesul la cont.'],
            'note' => 'Conectați Telegram în profil ca să primiți astfel de mesaje și acolo.',
        ],
        'moderation' => [
            'approved' => ['subject' => 'Profil aprobat — Women’s hub', 'line' => 'Profilul dvs. a fost aprobat: acum celelalte membre ale platformei vă văd.'],
            'rejected' => ['subject' => 'Decizia privind profilul — Women’s hub', 'line' => 'Vă mulțumim pentru interesul față de Women’s hub. În acest moment profilul dvs. nu a fost aprobat. Pentru detalii, scrieți-ne prin pagina „Contacte” de pe site.'],
            'revoked' => ['subject' => 'Acces închis — Women’s hub', 'line' => 'Accesul la Women’s hub a fost închis. Dacă aveți întrebări despre participare, scrieți-ne prin pagina „Contacte” de pe site.'],
            'button' => 'Deschide cabinetul',
            'note' => 'Conectați Telegram în profil ca să primiți astfel de mesaje și acolo.',
        ],
        'opened' => [
            'subject' => 'Achiziția tarifului :plan este deschisă pentru dumneavoastră — Women’s hub',
            'line' => 'Un manager a deschis pentru dumneavoastră achiziția tarifului :plan. Acum, pe pagina „Abonament” din cont, acest tarif are butonul „Cumpără”.',
            'button' => 'Mergeți la tarife',
            'note' => 'Plata se face cu cardul bancar, iar tariful se activează imediat după confirmarea băncii.',
        ],
    ],
];
