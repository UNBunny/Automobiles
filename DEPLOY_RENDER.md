# Деплой на Render.com

## Автоматический деплой

1. **Зарегистрируйтесь на [Render.com](https://render.com)**

2. **Создайте новый Blueprint:**
   - Нажмите "New +" → "Blueprint"
   - Подключите GitHub репозиторий
   - Render автоматически найдет `render.yaml`

3. **Дождитесь создания сервисов:**
   - PostgreSQL база данных
   - Web сервис с PHP + Nginx

4. **После деплоя выполните миграцию БД:**
   - Зайдите в Dashboard → automobiles-db → Shell
   - Выполните SQL из `postgres/init.sql`

## Переменные окружения

Render автоматически создаст:
- `DB_HOST` - адрес PostgreSQL
- `DB_NAME` - car_db
- `DB_USER` - nikita
- `DB_PASSWORD` - сгенерированный пароль

Добавьте вручную (если нужен OAuth):
- `OAUTH_CLIENT_ID` - ID приложения Yandex
- `OAUTH_CLIENT_SECRET` - Secret приложения Yandex
- `OAUTH_REDIRECT_URI` - https://your-app.onrender.com/admin/oauth-callback.php

## Настройка домена

1. В Render Dashboard → Settings → Custom Domain
2. Добавьте свой домен
3. Обновите DNS записи у регистратора

## Бесплатный план

- ✅ Web Service: 750 часов/месяц
- ✅ PostgreSQL: 90 дней бесплатно, потом $7/месяц
- ⚠️ Сервис засыпает после 15 минут неактивности
- ⚠️ Первый запрос после сна занимает ~30 секунд

## Альтернативы

Если нужна постоянная работа без засыпания:
- **Railway.app** - $5/месяц
- **DigitalOcean App Platform** - $5/месяц
- **Fly.io** - бесплатно до 3 приложений
