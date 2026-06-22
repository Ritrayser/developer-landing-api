# Developer Landing API

REST API для формы обратной связи лендинга разработчика.

## Быстрый локальный запуск (Laravel Sail)

1. Клонировать и установить зависимости
```bash
git clone <url>
cd developer-landing-api

docker run --rm \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs

2. Настроить .env

cp .env.example .env
Отредактируй .env (основные параметры):

APP_PORT=8000
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

CONTACT_THROTTLE=5,1
CONTACT_EMAIL=owner@example.com

3. Сгенерировать ключ приложения и создать БД

./vendor/bin/sail php artisan key:generate

touch database/database.sqlite

4. Запустить контейнеры и миграции

./vendor/bin/sail up -d

./vendor/bin/sail php artisan migrate

Приложение доступно на http://localhost:8000.

Ключ OpenAI не требуется используется встроенный fallback анализатор, который:

определяет тональность по позитивным/негативным словам;

определяет категорию по ключевым фразам («сотрудничество», «баг», «предложение» и т.п.).