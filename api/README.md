# Battery API Testing

HTTP-файлы для тестирования API приложения "Дневная Батарейка" с помощью REST Client.

## Требования

- Установлен плагин [REST Client](https://marketplace.visualstudio.com/items?itemName=humao.rest-client) для VS Code
- Запущено приложение на `http://localhost:8081`

## Структура файлов

### `battery-api.http`
Полный набор всех запросов в одном файле с автоматическим извлечением переменных (session cookie, publicHash).

**Использование:**
1. Выполните запрос "Claim Username" (первый запрос)
2. Переменные `@sessionCookie` и `@publicHash` будут автоматически извлечены
3. Остальные запросы используют эти переменные

### Отдельные файлы по сценариям

#### `01-claim-user.http`
Создание нового пользователя и получение publicHash.

**Эндпоинт:** `POST /api/claim`

**Что делает:**
- Создает пользователя с уникальным username
- Инициализирует батарейку на 16 часов (по умолчанию)
- Возвращает userId, username, publicHash
- Устанавливает session cookie

#### `02-battery-session.http`
Управление сессией батарейки (старт/стоп таймера).

**Эндпоинты:**
- `POST /api/battery/start` - запустить таймер
- `POST /api/battery/stop` - остановить таймер
- `GET /api/battery/status` - получить текущий статус

**Важно:** Перед использованием замените `@sessionCookie` на значение из Set-Cookie заголовка после claim.

#### `03-battery-settings.http`
Изменение настроек батарейки (емкость 1-24 часа).

**Эндпоинт:** `PATCH /api/battery/settings`

**Параметры:**
- `capacityHours` - емкость батарейки в часах (1-24)

#### `04-public-view.http`
Публичный просмотр батарейки по hash (без авторизации).

**Эндпоинт:** `GET /api/battery/public/{hash}`

**Примечание:** Замените `@publicHash` на реальное значение из ответа claim запроса.

#### `05-error-cases.http`
Тестирование всех возможных ошибок и валидации.

**Проверяемые ошибки:**
- 409 Conflict - username уже занят, батарейка уже активна/неактивна
- 422 Unprocessable Entity - валидация username, capacityHours
- 401 Unauthorized - доступ без авторизации
- 404 Not Found - несуществующий publicHash

## Quick Start

### Вариант 1: Использование battery-api.http

1. Откройте `battery-api.http`
2. Нажмите "Send Request" над первым запросом "Claim Username"
3. Автоматически извлекутся переменные для дальнейших запросов
4. Выполняйте остальные запросы по порядку

### Вариант 2: Использование отдельных файлов

1. Откройте `01-claim-user.http` и выполните запрос
2. Скопируйте значение `PHPSESSID` из заголовка `Set-Cookie` в ответе
3. Вставьте это значение в `@sessionCookie` в других файлах:
   ```
   @sessionCookie = PHPSESSID=3db2c8e73c434801360508b11dd91458
   ```
4. Скопируйте `publicHash` из ответа в `04-public-view.http`
5. Теперь можете выполнять запросы из других файлов

## Примеры Response

### Успешный claim
```json
{
  "success": true,
  "data": {
    "userId": "5f32eb83-b0ea-4ba5-8948-b150e43c0c38",
    "username": "testuser",
    "publicHash": "e3ea302904fefccb63c551d360b25f47"
  }
}
```

### Статус активной батарейки
```json
{
  "success": true,
  "data": {
    "capacity": 57600,
    "totalUsed": 0,
    "remaining": 57596,
    "percentage": 99.99,
    "isActive": true,
    "currentSessionStartedAt": "2025-10-12T08:40:15+00:00"
  }
}
```

### Публичный просмотр
```json
{
  "success": true,
  "data": {
    "username": "testuser",
    "capacity": 57600,
    "totalUsed": 120,
    "remaining": 57480,
    "percentage": 99.79,
    "isActive": false,
    "lastUpdated": "2025-10-12T08:45:30+00:00"
  }
}
```

## Типичный Flow тестирования

1. **Создание пользователя**
   ```
   POST /api/claim
   { "username": "myuser" }
   ```

2. **Запуск таймера**
   ```
   POST /api/battery/start
   ```

3. **Проверка статуса (батарейка активна)**
   ```
   GET /api/battery/status
   ```

4. **Остановка таймера**
   ```
   POST /api/battery/stop
   ```

5. **Проверка статуса (время накоплено)**
   ```
   GET /api/battery/status
   ```

6. **Изменение емкости**
   ```
   PATCH /api/battery/settings
   { "capacityHours": 12 }
   ```

7. **Публичный доступ**
   ```
   GET /api/battery/public/{publicHash}
   ```

## Полезные команды

### Очистка тестовых данных
```bash
docker compose exec php php bin/console doctrine:schema:drop --force
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

### Просмотр логов
```bash
docker compose logs -f php
docker compose logs -f nginx
```

### Проверка сессий в Redis
```bash
docker compose exec redis redis-cli
> KEYS *
> GET "sess:3db2c8e73c434801360508b11dd91458"
```

## Troubleshooting

### Ошибка "Not authenticated"
- Проверьте, что session cookie установлен
- Убедитесь, что используете правильный PHPSESSID
- Сессия могла истечь - выполните claim заново

### Ошибка "Username already taken"
- Используйте другой username
- Или очистите БД (см. команды выше)

### Таймаут подключения
- Проверьте, что контейнеры запущены: `docker compose ps`
- Проверьте порт: `curl http://localhost:8081`
