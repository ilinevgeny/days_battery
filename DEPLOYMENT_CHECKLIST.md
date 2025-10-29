# Production Deployment Checklist

Быстрый чеклист для деплоя. Подробности в [DEPLOYMENT.md](DEPLOYMENT.md).

## Перед деплоем

- [ ] Docker и Docker Compose установлены на сервере
- [ ] Открыты порты 80 и 443 на сервере
- [ ] SSH доступ к серверу работает

## Настройка DNS

- [ ] Создана A-запись для субдомена
- [ ] DNS распространился (проверить: `dig your-subdomain.yourdomain.com`)

## Настройка сервера

### 1. Клонирование репозитория

```bash
ssh user@your-server-ip
git clone <repo-url> days_battery
cd days_battery
```

### 2. Конфигурация

```bash
cp .env.prod .env.prod.local
nano .env.prod.local
```

Заполните:
- [ ] `DOMAIN` - ваш субдомен (без https://)
- [ ] `LETSENCRYPT_EMAIL` - ваш email
- [ ] `APP_SECRET` - сгенерировать: `php -r "echo bin2hex(random_bytes(32));"`
- [ ] `DEFAULT_URI` - https://ваш-домен
- [ ] `DATABASE_URL` - придумать и указать безопасный пароль БД (пользователь: `app`)

**Важно:** Тот же пароль БД нужно указать в `docker-compose.prod.yml`:

```bash
nano docker-compose.prod.yml
# Найти секцию postgres -> POSTGRES_PASSWORD и указать тот же пароль
```

- [ ] Пароль БД изменен в `docker-compose.prod.yml` (совпадает с `DATABASE_URL`)

### 3. Деплой

```bash
mv .env.prod.local .env.prod
./deploy.sh
```

## После деплоя

- [ ] Приложение доступно по https://your-domain
- [ ] SSL сертификат установлен (зеленый замок в браузере)
- [ ] Нет ошибок в логах: `docker compose -f docker-compose.prod.yml logs`

## Управление

**Примечание:** Замените `docker compose` на `docker-compose` (с дефисом) если используете старую версию.

```bash
# Логи
docker compose -f docker-compose.prod.yml logs -f

# Перезапуск
docker compose -f docker-compose.prod.yml restart

# Остановка
docker compose -f docker-compose.prod.yml down

# Обновление кода
git pull && ./deploy.sh
```

## Траблшутинг

### SSL не работает
```bash
docker compose -f docker-compose.prod.yml logs traefik
```

### Приложение не запускается
```bash
docker compose -f docker-compose.prod.yml logs php
docker compose -f docker-compose.prod.yml ps
```

### База данных недоступна
```bash
docker compose -f docker-compose.prod.yml exec postgres pg_isready
```

---

Полная документация: [DEPLOYMENT.md](DEPLOYMENT.md)
