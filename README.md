# Сервис бронирования

Платформа бронирования на Symfony 8.0 с API Platform, PostgreSQL, Redis и Docker.

## Быстрый старт

### Требования
- Docker 20.10+
- Docker Compose 2.0+

## Технологический стек

- **Symfony 8.0** - PHP фреймворк
- **API Platform** - REST/GraphQL API
- **Doctrine ORM** - Database abstraction
- **PostgreSQL 18** - Реляционная БД
- **Redis 7** - Кэш и очереди
- **Symfony Messenger** - Асинхронная обработка
- **Xdebug** - Отладчик PHP
- **Nginx** - Веб-сервер
- **Mailhog** - SMTP для разработки

### Запуск окружения

```bash
# 1. Клонировать репозиторий (если ещё не склонирован)
git clone <repo-url>
cd symfony-booking-platform

# 2. Запустить Docker контейнеры
docker compose up -d --build

# 3. Проверить статус контейнеров
docker compose ps

# 4. Установить зависимости Composer
docker compose exec php composer install

# 5. Выполнить миграции БД
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

# 6. (Опционально) Загрузить fixtures
docker compose exec php php bin/console doctrine:fixtures:load --no-interaction
```

### Доступ к сервисам

- **API / Web приложение**: http://localhost:8086
- **API Platform документация**: http://localhost:8086/api
- **Mailhog (SMTP UI)**: http://localhost:8025
- **PostgreSQL**: localhost:5432 (user: postgres, password: postgres, db: postgres)
- **Redis**: localhost:6379

## Разработка

### Полезные команды

```bash
# Просмотр логов всех контейнеров
docker compose logs -f

# Логи конкретного сервиса
docker compose logs -f php
docker compose logs -f worker

# Выполнение команд в контейнере PHP
docker compose exec php bash
docker compose exec php php bin/console about

# Перезапуск сервисов
docker compose restart

# Остановка окружения
docker compose down

# Остановка с удалением volumes (удалит данные БД!)
docker compose down -v
```

### Xdebug

Xdebug настроен и доступен на порту 9003. Конфигурация в `docker/php/xdebug.ini`.

**PHPStorm настройка:**
1. Settings → PHP → Debug → Xdebug → Port: 9003
2. Settings → PHP → Servers → добавить сервер с именем `booking_php`, host `localhost`, port `8086`
3. Path mappings: `/home/denis/Projects/Symfony/symfony-booking-platform` → `/app`

Для отключения Xdebug:
```bash
# В .env или через docker-compose
XDEBUG_MODE=off
```

### Работа с БД

```bash
# Создать миграцию
docker compose exec php php bin/console make:migration

# Применить миграции
docker compose exec php php bin/console doctrine:migrations:migrate

# Подключиться к PostgreSQL через psql
docker compose exec postgres psql -U postgres -d postgres

# Дамп схемы БД
docker compose exec php php bin/console doctrine:schema:update --dump-sql
```

### Messenger Worker

Worker запускается автоматически в контейнере `booking_worker` и обрабатывает асинхронные сообщения из Redis.

```bash
# Просмотр логов worker
docker compose logs -f worker

# Перезапуск worker
docker compose restart worker

# Вручную запустить обработку
docker compose exec php php bin/console messenger:consume async -vv
```

## Структура сервисов

### Сервисы Docker Compose

| Сервис | Образ | Порты | Описание |
|--------|-------|-------|----------|
| **php** | Custom (PHP 8.5-fpm) | - | PHP-FPM с Xdebug |
| **nginx** | nginx:alpine | 8086:80 | Веб-сервер |
| **postgres** | postgres:18 | 5432:5432 | База данных PostgreSQL |
| **redis** | redis:7 | 6379:6379 | Кэш и Messenger transport |
| **worker** | Custom (PHP 8.5-fpm) | - | Symfony Messenger worker |
| **mailhog** | mailhog/mailhog | 1025, 8025 | SMTP перехватчик для разработки |

### Health checks

Все критичные сервисы имеют health checks:
- **postgres**: pg_isready проверка
- **redis**: redis-cli ping

PHP и worker контейнеры ждут готовности зависимостей перед стартом.

## Отладка

### Типичные проблемы

**Контейнер не запускается:**
```bash
# Проверить логи
docker compose logs <service-name>

# Пересобрать образы
docker compose build --no-cache
docker compose up -d
```

**Ошибки БД подключения:**
```bash
# Проверить, что postgres готов
docker compose exec postgres pg_isready -U postgres -d postgres

# Проверить переменные окружения
docker compose exec php env | grep DATABASE
```

**Порты заняты:**
- Измените маппинг портов в `docker-compose.yml` (например, `5433:5432` вместо `5432:5432`)

## Лицензия

...

