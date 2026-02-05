# User (Пользователи)

## Задача

Управление пользователями системы — и тех, кто управляет платформой, и клиентов, которые делают бронирования.

## Назначение

Контекст User управляет пользователями и аутентификацией:
- создание/удаление пользователей
- управление ролями и правами
- привязка users к tenant

User определяет **политику доступа и права**, что важно для API-безопасности.

## Функционал

- регистрация / логин
- роли и права (admin, manager, staff, customer)
- профиль пользователя
- авторизация (JWT / API token)
- multi-tenancy поддержка

## Структура Domain слоя

### Entity (Агрегаты)
- **User** - корневой агрегат, представляющий пользователя системы

### Value Objects
- **UserId** - уникальный идентификатор пользователя (UUID v7)
- **Email** - email адрес с валидацией
- **HashedPassword** - захешированный пароль
- **UserRole** - enum ролей пользователя (ADMIN, MANAGER, STAFF, CUSTOMER)

### Repository
- **UserRepositoryInterface** - интерфейс репозитория для работы с User агрегатом

### Domain Services
- **PasswordHasherInterface** - сервис для хеширования и проверки паролей
- **UniqueEmailCheckerInterface** - сервис проверки уникальности email

### Domain Events
- **UserCreated** - пользователь создан
- **UserEmailChanged** - email изменён
- **UserPasswordChanged** - пароль изменён
- **UserActivated** - пользователь активирован
- **UserDeactivated** - пользователь деактивирован

## Основные элементы

- Сущность: `User`
- VO: `UserId`, `Email`, `PhoneNumber`, `HashedPassword`, `UserRole`
- Roles: `Admin`, `Manager`, `Staff`, `Customer`

## Взаимодействие

Используется в Booking и Tenant для проверки прав доступа и разграничения контекста арендаторов.

## Принципы DDD

- Все интерфейсы находятся в Domain слое
- Реализация инфраструктуры (ORM, Security) в Infrastructure слое
- Использование Value Objects для примитивов
- Domain Events для отслеживания важных изменений
- Агрегат User инкапсулирует бизнес-логику

---

## Аутентификация (JWT)

Этот проект использует `lexik/jwt-authentication-bundle` для выдачи и валидации JWT.

Ключевые моменты:
- Эндпоинт для логина: `/api/login` — обработка осуществляется через `security.yaml` (firewall с `json_login` и `check_path: /api/login`).
- Генерация токена происходит через Lexik success handler: `lexik_jwt_authentication.handler.authentication_success`.

Переменные окружения (пример в `.env`):

```dotenv
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=<your-passphrase>
```

Расположение ключей (по умолчанию): `config/jwt/private.pem` и `config/jwt/public.pem`.

Сгенерировать пару ключей можно командой:

```bash
php bin/console lexik:jwt:generate-keypair --overwrite
```

Если приватный ключ зашифрован, необходимо установить `JWT_PASSPHRASE` в окружении (например, в `.env.local`).

### Поведение эндпоинта `/api/login`
- `json_login` ожидает JSON {"email": "...", "password": "..."}.
- При успехе возвращается JWT (по умолчанию в поле `token` или в формате, настроенном в обработчике).
- При неуспехе возвращается 401.

---

## Как получить и использовать токен (пример)

Получение токена:

```bash
curl -s -X POST http://localhost:8086/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'
```

Пример ожидаемого ответа:

```json
{"token":"eyJ0eXAiOiJKV1QiLCJhbGc..."}
```

Использование токена для вызова защищённых эндпоинтов:

```bash
curl http://localhost:8086/api/users \
  -H "Authorization: Bearer <TOKEN>"
```

---

## Тестирование

### Быстрый smoke-тест (не требует БД)

Запустите упрощённый тест, который проверяет доступность эндпоинтов и наличие сервисов:

```bash
php bin/phpunit tests/User/Security/JwtAuthenticationSimpleTest.php
```

### Полные интеграционные тесты

Полные тесты могут требовать подготовки тестовой БД и миграций:

```bash
# Создать тестовую базу
php bin/console doctrine:database:create --env=test --if-not-exists

# Выполнить миграции для тестовой БД
php bin/console doctrine:migrations:migrate --env=test -n

# Запустить интеграционные тесты
php bin/phpunit tests/User/Security/JwtAuthenticationTest.php
```

В текущем проекте в `docker/php/docker-entrypoint.sh` уже создаётся тестовая база и выполняются миграции при запуске Docker контейнера.

Если проект запущен в Docker, добавьте префикс `docker compose exec php` к командам выше.

---

## Создание пользователей (тестовые и боевые)

1. Через fixtures (если доступны):

```bash
php bin/console doctrine:fixtures:load --env=test
```

2. Через консоль/command (если есть админская команда) — проверьте `src/User/Infrastructure/Console` или `src/User/Application/Command` на наличие команд создания пользователя.

3. Вручную через репозиторий в консольном скрипте или tinker:

```php
// пример использования репозитория в command/shell
$hashed = $passwordHasher->hashPassword($plainPassword);
$user = User::create(Email::fromString('user@example.com'), HashedPassword::fromString($hashed), 'First', 'Last');
$userRepository->save($user);
```

---

## Troubleshooting (быстрые решения)

1. Ошибка «Unable to load key \"config/jwt/private.pem\"»
   - Проверьте, что ключи существуют и пути в `.env` корректны.
   - Проверьте права доступа: `chmod 644 config/jwt/*`.
   - Если ключ зашифрован, убедитесь, что `JWT_PASSPHRASE` установлен.

2. Тесты возвращают 401 для защищённых эндпоинтов
   - Убедитесь, что `access_control` и firewall в `config/packages/security.yaml` корректно настроены.
   - Для интеграционных тестов создайте тестовую БД и выполните миграции.

3. Роут `/api/login` возвращает 404
   - Проверьте наличие файла `config/routes/lexik_jwt_authentication.yaml` с записью `api_login_check: path: /api/login`.
   - Очистите кэш: `php bin/console cache:clear --env=test`.

4. Сервисов Lexik нет в контейнере
   - Убедитесь, что бандл зарегистрирован в `config/bundles.php`.
   - Очистите и прогрейте кэш: `php bin/console cache:clear && php bin/console cache:warmup`.

---

## Полезные команды и отладка

```bash
# Проверить конфигурацию Lexik
php bin/console debug:config lexik_jwt_authentication

# Проверить security config
php bin/console debug:config security

# Проверить маршруты
docker compose exec php php bin/console debug:router | grep login

# Проверить, что JWT сервисы доступны
php bin/console debug:container lexik_jwt_authentication.key_loader
php bin/console debug:container lexik_jwt_authentication.encoder
```
