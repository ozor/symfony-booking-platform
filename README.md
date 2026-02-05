# Сервис бронирования

Платформа бронирования на Symfony 8.0 с API Platform, PostgreSQL, Redis и Docker.

## Быстрый старт

### Требования
- Docker 20.10+
- Docker Compose 2.0+

## Технологический стек

- **PHP 8.5** с FPM
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
- **PostgreSQL**: localhost:5432
- **Redis**: localhost:6379

Реквизиты доступа см. в `.env` файле.

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
docker compose exec postgres psql -U <user> -d <database>

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

**❌ "service php is not running container #1"**

Используйте автоматический скрипт исправления:
```bash
bash other-scripts/fix-docker-php.sh
```

Или вручную:
```bash
# Остановить контейнеры
docker compose down

# Пересобрать PHP контейнер
docker compose build --no-cache php

# Запустить заново
docker compose up -d

# Проверить статус
docker compose ps
```

Подробнее: `symfony-booking-platform-notes/DOCKER_PHP_QUICK_FIX.md`

**Контейнер не запускается:**
```bash
# Проверить логи
docker compose logs <service-name>

# Пересобрать образы
docker compose build --no-cache
docker compose up -d
```

**Docker daemon не запущен:**
```bash
# Запустить Docker
sudo systemctl start docker

# Включить автозапуск
sudo systemctl enable docker

# Проверить права доступа
sudo usermod -aG docker $USER
newgrp docker
```

**Ошибки БД подключения:**
```bash
# Проверить, что postgres готов
docker compose exec postgres pg_isready -U postgres -d postgres

# Проверить переменные окружения
docker compose exec php env | grep DATABASE

# Применить миграции
docker compose exec php php bin/console doctrine:migrations:migrate
```

**Порты заняты:**
```bash
# Проверить, какой процесс использует порт
sudo lsof -i :8086
sudo lsof -i :5432

# Или изменить маппинг портов в docker-compose.yml
# Например: 5433:5432 вместо 5432:5432
```

### Полезные скрипты

Доступны автоматические скрипты для решения проблем:

```bash
# Автоматическое исправление Docker проблем
bash other-scripts/fix-docker-php.sh

# Диагностика Docker
bash other-scripts/diagnose-docker.sh

# Запуск контейнеров
bash other-scripts/start-docker.sh
```

## Лицензия

[Creative Commons Attribution-NonCommercial 4.0 International (CC BY-NC 4.0)](https://creativecommons.org/licenses/by-nc/4.0/). 

