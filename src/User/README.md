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

