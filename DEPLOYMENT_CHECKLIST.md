# Production Deployment Checklist

Быстрый чеклист для деплоя без SSL. Подробности в [DEPLOYMENT.md](DEPLOYMENT.md).

## Перед деплоем

- [ ] Docker и Docker Compose установлены на сервере
- [ ] Порт 8080 свободен (или выбран другой порт)
- [ ] SSH доступ к серверу работает

## Настройка DNS (опционально)

- [ ] Создана A-запись для субдомена (если используете домен)
- [ ] DNS распространился: `dig your-subdomain.yourdomain.com`

Или просто используйте IP-адрес сервера.

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
- [ ] `APP_SECRET` - сгенерировать: `php -r "echo bin2hex(random_bytes(32));"`
- [ ] `DEFAULT_URI` - `http://your-domain:8080` или `http://IP:8080`
- [ ] `DATABASE_URL` - придумать и указать безопасный пароль БД

**Важно:** Пароль БД нужно указать в `docker-compose.prod.yml`:

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

- [ ] Приложение доступно по `http://your-domain:8080` или `http://IP:8080`
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

### Порт 8080 уже занят
```bash
sudo netstat -tulpn | grep ':8080'
# Измените порт в docker-compose.prod.yml на другой (например 8081)
```

### Приложение не запускается
```bash
docker compose -f docker-compose.prod.yml logs php
docker compose -f docker-compose.prod.yml ps
```

### База данных недоступна
```bash
docker compose -f docker-compose.prod.yml exec postgres pg_isready -U app
```

---

**Примечание:** Приложение работает на HTTP (порт 8080) без SSL из-за конфликта с VPN на порту 443.

Полная документация: [DEPLOYMENT.md](DEPLOYMENT.md)
