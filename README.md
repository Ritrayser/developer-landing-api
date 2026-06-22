# Developer Landing API

## 1.  Как запустить проект

### Требования
- Docker Desktop + WSL2 (Windows) или Linux/macOS
- Git

### Установка и запуск

# Клонировать репозиторий
git clone <url>
cd developer-landing-api

# Установить зависимости через Composer (без локального PHP)
docker run --rm \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs

# Создать файл окружения
cp .env.example .env
Настройка .env (ключевые переменные):

APP_PORT=8000
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

CONTACT_THROTTLE=5,1
CONTACT_EMAIL=owner@example.com
bash
# Сгенерировать ключ приложения
./vendor/bin/sail php artisan key:generate

# Создать файл базы данных SQLite
touch database/database.sqlite

# Запустить контейнеры
./vendor/bin/sail up -d

# Выполнить миграции
./vendor/bin/sail php artisan migrate
Приложение будет доступно по адресу http://localhost:8000.

2. Стек технологий
Backend:

PHP 8.3

Laravel 11

Laravel Sail (Docker‑окружение)

SQLite (хранение данных)

Monolog (логирование)

AI:

Встроенный анализатор на основе ключевых слов (fallback)

Опционально: OpenAI API (gpt-3.5-turbo) – включается автоматически при наличии ключа в .env

3. 🏗️ Архитектура
Структура проекта (ключевые файлы)
text
app/
├── Http/
│   ├── Controllers/
│   │   ├── ContactController.php    – обработка POST /api/contact
│   │   ├── HealthController.php     – healthcheck
│   │   └── MetricsController.php    – статистика
│   ├── Middleware/
│   │   └── CorsMiddleware.php       – CORS‑заголовки
│   └── Requests/
│       └── ContactRequest.php       – правила валидации
├── Models/
│   └── Contact.php                  – модель Eloquent
└── Services/
    ├── AIService.php                – AI‑анализ (OpenAI / fallback)
    └── ContactService.php           – бизнес‑логика обработки обращения

routes/
└── api.php                          – маршруты API

bootstrap/
└── app.php                          – конфигурация ядра, rate limiter

config/
└── logging.php                      – добавлен канал 'contact'

database/
└── migrations/
    └── ..._create_contacts_table.php
Паттерны проектирования
Слоистая архитектура: Controllers → Services → Models.

Dependency Injection: сервисы внедряются через конструкторы.

Middleware: CORS, Rate Limiting.

Graceful fallback: AI‑сервис автоматически переключается на локальный анализатор при недоступности внешнего API.

Почему выбраны именно эти технологии
Laravel + Sail – даёт быстрый старт, встроенные инструменты валидации, кеширования, rate limiting и удобное Docker‑окружение.

SQLite – не требует отдельного сервера БД, файловая база сразу готова к работе, при этом поддерживает SQL и Eloquent.

Monolog – гибкое логирование, легко добавить отдельный файл для логов обращений.

Встроенный Rate Limiter – прост в настройке, не нужно писать своё решение.

Fallback‑анализатор – гарантирует работоспособность AI‑функции без внешних сервисов и ключей.

4. Реализация API
Эндпоинты
Метод	Путь	Описание
POST	/api/contact	Отправить обращение
GET	/api/health	Проверка состояния сервера
GET	/api/metrics	Статистика обращений
Примеры запросов
Успешное обращение
bash
curl -X POST http://localhost:8000/api/contact \
  -H "Content-Type: application/json" \
  -d '{"name":"Иван","phone":"+79001234567","email":"ivan@test.com","comment":"Отличный лендинг, хочу сотрудничать!"}'
Ответ (200):

json
{
    "status": "success",
    "message": "Сообщение успешно отправлено",
    "sentiment": "positive",
    "category": "partnership"
}
Ошибка валидации (422)
bash
curl -X POST http://localhost:8000/api/contact \
  -H "Content-Type: application/json" \
  -d '{"name":"","phone":"abc","email":"bad","comment":"short"}'
Ответ: JSON с полем errors, описывающим каждое нарушение.

Rate limit (429)
При превышении 5 запросов в минуту с одного IP возвращается:

json
{
    "message": "Too Many Attempts."
}
Валидация и обработка ошибок
Правила заданы в App\Http\Requests\ContactRequest: имя (2‑100 символов), телефон (формат 7‑15 цифр, с + опционально), email, комментарий (10‑2000 символов).

При ошибке валидации автоматически возвращается 422 Unprocessable Entity.

Все исключения логируются в канал contact; клиенту возвращается 500 Internal Server Error с JSON‑сообщением.

5. AI-интеграция
Используемые AI-инструменты
Встроенный анализатор (fallback) – работает без интернета и ключей.

Опционально: OpenAI API (gpt-3.5-turbo) – при добавлении ключа OPENAI_API_KEY в .env.

Зачем нужен fallback и как он работает
Если ключ OpenAI не указан или API недоступен, AI‑сервис не падает, а переключается на локальный анализатор.

Анализатор определяет тональность по словарям позитивных/негативных слов (например, «отлично» → positive, «плохо» → negative).

Категория определяется по ключевым фразам («сотрудничество» → partnership, «баг» → bug, «предложение» → feature, «жалоба» → complaint, иначе general).

Промпты (для OpenAI, если используется)
Системный промпт:

Ты анализируешь обращения. Верни только JSON: {"sentiment":"positive/negative/neutral","category":"general/partnership/bug/feature/complaint"}
Пользовательский промпт – текст комментария из формы.

В текущей поставке ключ не используется – достаточно встроенного анализатора.

6. Что сделано с помощью AI
При разработке использовался AI‑ассистент для ускорения написания кода.

Сгенерированные части кода:

Базовая структура AIService (метод fallbackAnalysis и вызов OpenAI).

Первичная версия ContactService.

Использованные промпты:

«Создай сервис для Laravel с OpenAI и fallback на ключевых словах, который анализирует тональность и категорию обращения».

Что исправлялось вручную:

Формат ответа AI (строгий JSON).

Обработка ошибок и логирование.

Интеграция с моделями Eloquent.

Настройка валидации, маршрутов, CORS, rate limiting.

Приведение кода к стандартам проекта и чистка от лишнего.

7. Хранение данных
База данных
SQLite (файл database/database.sqlite).

Таблица contacts создаётся миграцией.

Все обращения сохраняются вместе с результатами AI‑анализа.

Логирование
Настроен отдельный лог‑канал contact в config/logging.php.

Записи попадают в storage/logs/contact.log.

Логируются: успешные обработки, ошибки, эмулированные email‑уведомления.

Rate limiting
Реализован стандартным механизмом Laravel через throttle middleware.

Настройки берутся из .env: CONTACT_THROTTLE=5,1 означает 5 запросов в минуту с одного IP.

Определение в bootstrap/app.php через RateLimiter::for.

Статистика
Эндпоинт /api/metrics возвращает общее число обращений и их распределение по категориям.

Данные берутся напрямую из таблицы contacts с помощью Eloquent (count, groupBy).