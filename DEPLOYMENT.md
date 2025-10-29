# Days Battery - Production Deployment Guide

Простая инструкция по деплою приложения на VPS без SSL (HTTP на порту 8080).

## Предварительные требования

- VPS с установленным Docker и Docker Compose
- Доменное имя с возможностью настройки DNS (опционально)
- SSH доступ к серверу

## Шаг 1: Настройка DNS (опционально)

Если хотите использовать доменное имя, создайте A-запись:

```
Тип: A
Имя: battery (или другой субдомен)
Значение: <IP вашего VPS>
TTL: 3600
```

Приложение будет доступно по `http://battery.yourdomain.com:8080`

Или можно использовать просто IP: `http://123.45.67.89:8080`

**Проверка DNS:**
```bash
dig battery.yourdomain.com
```

## Шаг 2: Подготовка сервера

Подключитесь к серверу по SSH:

```bash
ssh user@your-server-ip
```

Клонируйте репозиторий:

```bash
git clone <URL вашего репозитория> days_battery
cd days_battery
```

## Шаг 3: Конфигурация окружения

Скопируйте файл конфигурации:

```bash
cp .env.prod .env.prod.local
```

Отредактируйте `.env.prod.local`:

```bash
nano .env.prod.local
```

Заполните следующие значения:

```env
# Секретный ключ приложения (сгенерируйте новый!)
APP_SECRET=<сгенерированный секрет>

# URL приложения (с портом 8080)
DEFAULT_URI=http://battery.yourdomain.com:8080
# Или с IP: DEFAULT_URI=http://123.45.67.89:8080

# Данные БД: Придумайте свой пароль и замените ChangeMe_SecurePassword123
DATABASE_URL="postgresql://app:ChangeMe_SecurePassword123@postgres:5432/days_battery?serverVersion=16&charset=utf8"
```

### Генерация APP_SECRET

```bash
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
```

Скопируйте вывод в `APP_SECRET`.

### Настройка пароля базы данных

**Важно:** Пароль БД нужно указать в ДВУХ местах:

1. **В `.env.prod.local`** - в `DATABASE_URL`
2. **В `docker-compose.prod.yml`** - в секции `postgres -> POSTGRES_PASSWORD`

Откройте второй файл:

```bash
nano docker-compose.prod.yml
```

Найдите секцию `postgres` и замените пароль:

```yaml
postgres:
  environment:
    POSTGRES_DB: days_battery
    POSTGRES_USER: app
    POSTGRES_PASSWORD: ВАШ_ПАРОЛЬ_ЗДЕСЬ  # Используйте тот же пароль из DATABASE_URL
```

**Как это работает:**
- Вы сами придумываете безопасный пароль
- При первом запуске Docker создаст PostgreSQL с этим паролем
- Приложение будет подключаться к БД используя пароль из `DATABASE_URL`
- Пароли в обоих файлах должны совпадать!

## Шаг 4: Деплой

Переименуйте файл конфигурации:

```bash
mv .env.prod.local .env.prod
```

Запустите деплой:

```bash
./deploy.sh
```

Скрипт автоматически:
- Определит версию Docker Compose (старую или новую)
- Проверит конфигурацию
- Остановит старые контейнеры (если есть)
- Соберет Docker образы
- Запустит все сервисы
- Применит миграции БД

## Шаг 5: Проверка

После завершения деплоя (2-3 минуты):

1. Откройте в браузере: `http://battery.yourdomain.com:8080` (или `http://IP:8080`)
2. Приложение должно быть доступно

**Примечание:** Приложение работает на HTTP без SSL из-за того, что порт 443 занят VPN-сервером.

## Управление приложением

**Примечание:** В примерах используется `docker compose` (новый синтаксис). Если у вас старая версия, замените на `docker-compose` (с дефисом). Скрипт `deploy.sh` автоматически определяет правильную команду.

### Просмотр логов

```bash
docker compose -f docker-compose.prod.yml logs -f
```

Логи конкретного сервиса:
```bash
docker compose -f docker-compose.prod.yml logs -f php
docker compose -f docker-compose.prod.yml logs -f nginx
```

### Перезапуск

```bash
docker compose -f docker-compose.prod.yml restart
```

### Остановка

```bash
docker compose -f docker-compose.prod.yml down
```

### Запуск

```bash
docker compose -f docker-compose.prod.yml up -d
```

### Доступ к контейнеру PHP

```bash
docker compose -f docker-compose.prod.yml exec php sh
```

Внутри контейнера доступны Symfony команды:
```bash
php bin/console cache:clear
php bin/console doctrine:migrations:status
```

## Обновление приложения

Для обновления кода на сервере:

```bash
cd days_battery
git pull
./deploy.sh
```

Скрипт пересоберет образы и перезапустит контейнеры.

## Резервное копирование БД

### Создать бэкап

```bash
docker compose -f docker-compose.prod.yml exec postgres pg_dump -U app days_battery > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Восстановить из бэкапа

```bash
cat backup_20250101_120000.sql | docker compose -f docker-compose.prod.yml exec -T postgres psql -U app days_battery
```

## Траблшутинг

### Приложение не запускается

1. Проверьте статус контейнеров:
   ```bash
   docker compose -f docker-compose.prod.yml ps
   ```

2. Проверьте логи PHP:
   ```bash
   docker compose -f docker-compose.prod.yml logs php
   ```

3. Проверьте подключение к БД:
   ```bash
   docker compose -f docker-compose.prod.yml exec php php bin/console dbal:run-sql "SELECT 1"
   ```

### База данных не доступна

```bash
docker compose -f docker-compose.prod.yml exec postgres pg_isready -U app
```

Если не работает, перезапустите контейнер БД:
```bash
docker compose -f docker-compose.prod.yml restart postgres
```

### Порт 8080 уже занят

Проверьте что занимает порт:
```bash
sudo netstat -tulpn | grep ':8080'
```

Остановите конфликтующий сервис или измените порт в `docker-compose.prod.yml`:
```yaml
nginx:
  ports:
    - "8081:80"  # Используйте другой порт
```

Не забудьте обновить `DEFAULT_URI` в `.env.prod`.

## Архитектура

```
┌─────────────────┐
│   Internet      │
└────────┬────────┘
         │ :8080 (HTTP)
    ┌────▼─────────┐
    │    Nginx     │ (web server)
    └────┬─────────┘
         │
    ┌────▼─────────┐
    │   PHP-FPM    │ (Symfony app)
    └──┬─────────┬─┘
       │         │
  ┌────▼───┐ ┌──▼─────┐
  │Postgres│ │ Redis  │
  └────────┘ └────────┘
```

**Примечание:** Traefik и SSL убраны из-за конфликта с VPN на порту 443.

## Безопасность

**Важные рекомендации:**

1. Никогда не коммитьте `.env.prod` в git
2. Используйте сложные пароли для БД
3. Регулярно обновляйте Docker образы
4. Настройте firewall для ограничения доступа по SSH
5. Для production желательно настроить SSL (можно использовать другой порт или reverse proxy)

## Добавление SSL в будущем

Если захотите добавить SSL позже:

**Вариант 1:** Переместить VPN на другой порт (8443), освободить 443 для Traefik
**Вариант 2:** Использовать Cloudflare для SSL (бесплатно)
**Вариант 3:** Настроить отдельный Nginx как reverse proxy с SSL, проксирующий на :8080

## Поддержка

При возникновении проблем:
1. Проверьте логи всех сервисов
2. Убедитесь, что все переменные окружения заполнены
3. Проверьте статус Docker контейнеров
4. Убедитесь, что порт 8080 открыт в firewall

---

**Готово!** Ваше приложение работает на `http://your-domain:8080`
