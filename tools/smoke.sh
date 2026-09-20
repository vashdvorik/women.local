#!/usr/bin/env bash
#
# Реальные HTTP-запросы к поднятому серверу: проверяет, что публичные страницы
# и границы админки отвечают ожидаемыми кодами. Дополняет PHPUnit-тесты (они
# ходят в приложение без веб-сервера). Запускается как `npm run smoke`.
#
# Сверять список ниже с `php artisan route:list --except-vendor`: добавил
# публичный роут — добавь его сюда. Проверки не зависят от содержимого БД:
# пустой раздел отдаёт 200 и состояние «Раздел готовится».
#
# Требует: применённые миграции локальной БД (`php artisan migrate`).

set -u

PORT="${SMOKE_PORT:-8123}"
HOST="127.0.0.1"
BASE="http://${HOST}:${PORT}"

cd "$(dirname "$0")/.."

php artisan serve --host="$HOST" --port="$PORT" >/dev/null 2>&1 &
SERVER_PID=$!
trap 'kill $SERVER_PID 2>/dev/null' EXIT

# Ждём, пока сервер поднимется.
for _ in $(seq 1 40); do
    if curl -s -o /dev/null "${BASE}/"; then break; fi
    sleep 0.25
done

# «путь ожидаемый_код»
CHECKS=(
    # Публичный сайт (тема miro)
    "/ 200"
    "/about 200"
    "/about/priorities 200"
    "/members 200"
    "/experts 200"
    "/events 200"
    "/partners 200"
    "/contact 200"
    "/projects 200"
    "/opportunities 200"
    "/media/photos 200"
    "/media/videos 200"
    "/media/publications 200"
    "/about/leadership 200"
    "/gala 200"

    # Несуществующие материалы
    "/media/publications/does-not-exist 404"
    "/opportunities/does-not-exist 404"
    "/media/photos/does-not-exist 404"

    # Кабинет участницы: без сессии уводит на вход
    "/app/account 302"
    "/app/account/opportunities 302"

    # Админка: без входа — на форму входа, вход открыт
    "/login 200"
    "/admin 302"
    "/admin/news 302"
    "/admin/profiles 302"
    "/admin/member-posts 302"
    "/admin/cabinets 302"
    "/admin/cabinets/settings 302"
    "/admin/experts 302"
    "/admin/events 302"
    "/admin/settings 302"
    "/admin/statistics 302"
)

FAILED=0
for check in "${CHECKS[@]}"; do
    path="${check% *}"
    expected="${check#* }"
    actual=$(curl -s -o /dev/null -w '%{http_code}' "${BASE}${path}")
    if [ "$actual" = "$expected" ]; then
        printf '  ok   %-38s %s\n' "$path" "$actual"
    else
        printf '  FAIL %-38s ожидали %s, получили %s\n' "$path" "$expected" "$actual"
        FAILED=1
    fi
done

# Содержимое: «путь|подстрока» (разделитель — «|»). Языки сайта лежат в HTML сразу
# (data-lang), поэтому проверяем все три.
CONTENT=(
    "/experts|Наши эксперты"
    "/experts|Our experts"
    "/experts|Experții noștri"
    "/events|Встречи, которые развивают"
    "/|action=\"${BASE}/subscribe\""
    "/projects|Раздел готовится"
    "/login|Вход в панель"
)
for check in "${CONTENT[@]}"; do
    path="${check%%|*}"
    needle="${check#*|}"
    body=$(curl -s "${BASE}${path}")
    # Пустой раздел уступает место карточкам, когда в админке появляются материалы.
    if [ "$path" = "/projects" ] && ! printf '%s' "$body" | grep -qF -- "$needle"; then
        if printf '%s' "$body" | grep -qF -- 'miro-event-card'; then
            printf '  ok   %-24s есть карточки проектов\n' "$path"
            continue
        fi
    fi
    if printf '%s' "$body" | grep -qF -- "$needle"; then
        printf '  ok   %-24s содержит «%s»\n' "$path" "$needle"
    else
        printf '  FAIL %-24s нет «%s»\n' "$path" "$needle"
        FAILED=1
    fi
done

# Подписка: без токена CSRF запрос отклоняется, а не молча принимается.
csrf=$(curl -s -o /dev/null -w '%{http_code}' -X POST "${BASE}/subscribe" -d 'email=smoke@example.test&consent=1')
if [ "$csrf" = "419" ]; then
    printf '  ok   %-38s 419 без CSRF-токена\n' "POST /subscribe"
else
    printf '  FAIL %-38s ожидали 419, получили %s\n' "POST /subscribe" "$csrf"
    FAILED=1
fi

if [ "$FAILED" -ne 0 ]; then
    echo "smoke: провал"
    exit 1
fi

echo "smoke: все проверки зелёные"
