# Days Battery (Дневная батарейка)

API приложение для отслеживания ежедневного времени активности пользователей.

## Требования

- Docker
- Docker Compose
- Make (опционально, для удобства)

## Быстрый старт

### 1. Запуск проекта

```bash
# Сборка и запуск контейнеров
make build
make up

# Установка зависимостей
make install

# Создание базы данных
make db-create

# Запуск миграций
make migrate
```

### 2. Доступ к приложению

- **Web**: http://localhost:8081
- **API**: http://localhost:8081/api

### 3. Разработка

```bash
# Вход в PHP контейнер
make shell

# Запуск тестов
make test

# Проверка качества кода
make check

# Авто-исправление стиля кода
make fix

# Просмотр логов
make logs

# Перезапуск контейнеров
make restart

# Остановка контейнеров
make down
```

## Архитектура

Проект следует принципам DDD (Domain-Driven Design) с разделением на bounded contexts:

- **Battery Management Context** - основная доменная логика управления батарейками
- **Identity & Access Context** - управление пользователями и сессиями (упрощенная версия для MVP)

## Стек технологий

- PHP 8.3
- Symfony 7.2
- PostgreSQL 16
- Redis 7 (sessions)
- Nginx
- Docker & Docker Compose

## Код-стайл

Проект следует стандарту **PER Coding Style 3.0** с обязательными:
- `declare(strict_types=1);` в каждом файле
- Type hints для всех параметров и возвратных значений
- PHPStan level 8

## Команды Symfony

```bash
# Через Make
make console CMD="debug:router"
make console CMD="cache:clear"

# Напрямую из контейнера
docker-compose exec php bin/console debug:router
```

## База данных

```bash
# Создать БД
make db-create

# Удалить БД
make db-drop

# Создать миграцию
make console CMD="make:migration"

# Применить миграции
make migrate
```

## Тестирование

```bash
# Все тесты
make test

# Конкретный тест
docker-compose exec php vendor/bin/phpunit tests/Unit/Battery/Domain/Entity/BatteryTest.php

# С фильтром по методу
docker-compose exec php vendor/bin/phpunit --filter testMethodName
```
