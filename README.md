# Bona ecommerce

Інтернет-магазин дверей на Laravel 13, PHP 8.4 і Vite 8.

## Вимоги

- PHP 8.4 з `dom`, `fileinfo`, `gd`, `intl`, `mbstring`, `pdo_mysql`, `zip`;
- Composer 2;
- Node.js 24 і npm 11;
- MySQL або MariaDB.

## Локальний запуск

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

Заповніть у `.env` підключення до БД, пошти, LiqPay, Instagram OAuth та інших зовнішніх сервісів. Для розстрочки Monobank потрібні `MONOBANK_API_URL`, `MONOBANK_CLIENT_SECRET`, `MONOBANK_CLIENT_STORE_ID` і виданий банком `MONOBANK_POINT_ID`; для PrivatBank — `PRIVATBANK_STORE_ID` та `PRIVATBANK_PASSWORD`. Секрети не повинні потрапляти до Git.

## Telegram-сповіщення

Створіть бота через `@BotFather`, додайте його до робочого чату/групи й заповніть у `.env`:

```dotenv
TELEGRAM_NOTIFICATIONS_ENABLED=true
TELEGRAM_BOT_TOKEN=
TELEGRAM_CHAT_ID=
TELEGRAM_MESSAGE_THREAD_ID=
```

`TELEGRAM_MESSAGE_THREAD_ID` потрібен лише для конкретної теми форум-групи. Перевірка надсилає одне безпечне тестове повідомлення:

```bash
php artisan telegram:test
```

Заявки, замовлення та зміни статусів оплат ставляться в зашифровану чергу. Telegram не може зірвати збереження замовлення, але для доставки повідомлень постійний queue worker має працювати.

Перевірити наявність і узгодженість платіжних налаштувань без показу секретів і без списання коштів:

```bash
php artisan payments:diagnose --strict
php artisan payments:reconcile
```

## Черги

Листи та фонові завдання використовують database queue. Після міграцій запустіть постійний worker під Supervisor або systemd:

```bash
php artisan queue:work --sleep=1 --tries=3 --timeout=120
```

Після кожного deploy перезапускайте worker командою `php artisan queue:restart`.

## Перевірки

```bash
composer audit --locked
./vendor/bin/pint --test
php artisan test
npm audit --audit-level=high
npm run build
```

CI виконує ці перевірки на PHP 8.4, Node.js 24 і MariaDB 11.8.

## Deploy

Production отримує вже зібраний і перевірений архів із CI. Composer та npm на production-сервері не запускаються. Реліз активується атомарним перемиканням symlink і має health-check та rollback.

Повна інструкція, перелік GitHub Secrets, перший запуск і rollback: [`deploy/README.md`](deploy/README.md).
