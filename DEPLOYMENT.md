# Days Battery - Production Deployment Guide

Простая инструкция по деплою приложения на VPS с автоматическим SSL от Let's Encrypt.

## Предварительные требования

- VPS с установленным Docker и Docker Compose
- Доменное имя с возможностью настройки DNS
- SSH доступ к серверу

## Шаг 1: Настройка DNS

Создайте A-запись для вашего субдомена, указывающую на IP вашего VPS:

```
Тип: A
Имя: battery (или другой субдомен)
Значение: <IP вашего VPS>
TTL: 3600 (или по умолчанию)
```

Пример: `battery.yourdomain.com` → `123.45.67.89`

**Важно:** Дождитесь распространения DNS (обычно 5-15 минут). Проверить можно командой:
```bash
dig battery.yourdomain.com
# или
nslookup battery.yourdomain.com
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
# Ваш домен (БЕЗ https://)
DOMAIN=battery.yourdomain.com

# Email для уведомлений Let's Encrypt
LETSENCRYPT_EMAIL=your-email@example.com

# Секретный ключ приложения (сгенерируйте новый!)
APP_SECRET=<сгенерированный секрет>

# URL приложения
DEFAULT_URI=https://battery.yourdomain.com

# Данные БД: Придумайте свой пароль и замените ChangeMe_SecurePassword123
# Пользователь БД: app (не меняйте)
DATABASE_URL="postgresql://app:ChangeMe_SecurePassword123@postgres:5432/days_battery?serverVersion=16&charset=utf8"
```

### Генерация APP_SECRET

```bash
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
```

Скопируйте вывод в `APP_SECRET`.

### Настройка пароля базы данных

**Важно:** Пароль БД нужно указать в ДВУХ местах:

1. **В `.env.prod.local`** (который вы сейчас редактируете) - в `DATABASE_URL`
2. **В `docker-compose.prod.yml`** - в секции `postgres -> environment -> POSTGRES_PASSWORD`

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
- Проверит конфигурацию
- Остановит старые контейнеры (если есть)
- Соберет Docker образы
- Запустит все сервисы
- Применит миграции БД
- Настроит SSL через Let's Encrypt

## Шаг 5: Проверка

После завершения деплоя (1-2 минуты):

1. Откройте в браузере: `https://battery.yourdomain.com`
2. Проверьте наличие SSL сертификата (замок в адресной строке)

## Управление приложением

### Просмотр логов

```bash
docker compose -f docker-compose.prod.yml logs -f
```

Логи конкретного сервиса:
```bash
docker compose -f docker-compose.prod.yml logs -f php
docker compose -f docker-compose.prod.yml logs -f nginx
docker compose -f docker-compose.prod.yml logs -f traefik
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
docker compose -f docker-compose.prod.yml exec postgres pg_dump -U prod_user days_battery > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Восстановить из бэкапа

```bash
cat backup_20250101_120000.sql | docker compose -f docker-compose.prod.yml exec -T postgres psql -U prod_user days_battery
```

## Траблшутинг

### SSL сертификат не выдается

1. Убедитесь, что DNS настроен правильно:
   ```bash
   dig battery.yourdomain.com
   ```

2. Проверьте логи Traefik:
   ```bash
   docker compose -f docker-compose.prod.yml logs traefik
   ```

3. Убедитесь, что порты 80 и 443 открыты и не заняты другими процессами:
   ```bash
   sudo netstat -tulpn | grep ':80\|:443'
   ```

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
docker compose -f docker-compose.prod.yml exec postgres pg_isready -U prod_user
```

Если не работает, перезапустите контейнер БД:
```bash
docker compose -f docker-compose.prod.yml restart postgres
```

## Архитектура

```
┌─────────────────┐
│   Internet      │
└────────┬────────┘
         │ :80, :443
    ┌────▼─────────┐
    │   Traefik    │ (SSL, routing)
    └────┬─────────┘
         │
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

## Безопасность

**Важные рекомендации:**

1. Никогда не коммитьте `.env.prod` в git
2. Используйте сложные пароли для БД
3. Регулярно обновляйте Docker образы
4. Настройте firewall для ограничения доступа по SSH
5. Включите автоматические обновления безопасности на сервере

## Дополнительные настройки

### Изменение порта PostgreSQL

По умолчанию PostgreSQL не доступен извне. Если нужен внешний доступ (не рекомендуется для production), добавьте в `docker-compose.prod.yml` в секцию `postgres`:

```yaml
ports:
  - "5432:5432"
```

### Увеличение лимитов памяти PHP

Отредактируйте `docker/php/php.ini`:

```ini
memory_limit = 512M
```

Пересоберите контейнеры:
```bash
./deploy.sh
```

## Поддержка

При возникновении проблем:
1. Проверьте логи всех сервисов
2. Убедитесь, что все переменные окружения заполнены
3. Проверьте статус Docker контейнеров

---

**Готово!** Ваше приложение работает на `https://battery.yourdomain.com` с автоматическим SSL.
