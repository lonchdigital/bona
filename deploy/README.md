# Production deploy

Ця схема відправляє на production один незмінний архів. PHP-залежності та frontend assets збираються в GitHub Actions, а не на production-сервері.

## Структура на сервері

```text
/var/www/bona/
├── current -> releases/<active-release>
├── previous -> releases/<previous-release>
├── incoming/
├── releases/
└── shared/
    ├── .env
    └── storage/
```

Nginx завжди дивиться в `/var/www/bona/current/public`. Кожен реліз отримує спільні `.env` і `storage` через symlink.

## Одноразова підготовка

1. Встановити PHP 8.4 CLI/FPM і extensions: `bcmath`, `curl`, `dom`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `xml`, `zip`.
2. Встановити `curl`, `flock`, `tar`, `sha256sum`, Nginx і Supervisor.
3. Створити каталоги `releases`, `incoming`, `shared/storage` і надати користувачу deploy та `www-data` потрібні права.
4. Перенести production `.env` у `shared/.env`, не додаючи його до Git.
5. Зробити повний backup бази даних і `shared/storage`.
6. Перенести чинний production-код у `releases/bootstrap`, під'єднати до нього `shared/.env` і `shared/storage`, потім створити `current -> releases/bootstrap`. Скрипт навмисно не автоматизує цей крок, бо без розуміння теперішньої структури сервера це небезпечно.
7. Адаптувати й увімкнути [`nginx-bona.conf.example`](nginx-bona.conf.example) та [`supervisor-bona.conf.example`](supervisor-bona.conf.example).
8. Додати scheduler для користувача застосунку:

   ```cron
   * * * * * cd /var/www/bona/current && /usr/bin/php8.4 artisan schedule:run >> /dev/null 2>&1
   ```

Перед першим релізом запустити:

```bash
PHP_BIN=/usr/bin/php8.4 bash deploy/preflight.sh /var/www/bona
```

## Production `.env`

Перевірити щонайменше:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com
LOG_LEVEL=warning
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_SERIALIZATION=json
SESSION_SECURE_COOKIE=true
INSTAGRAM_APP_ID=
INSTAGRAM_APP_SECRET=
INSTAGRAM_GRAPH_VERSION=v26.0
```

`APP_KEY` та назву `SESSION_COOKIE` треба зберегти з чинного production. Не можна копіювати `.env.example` поверх production `.env`. Перехід `SESSION_SERIALIZATION=json` завершить раніше створені PHP-серіалізовані сесії, тому користувачам доведеться увійти знову.

## Instagram-стрічка

Інтеграція використовує **Instagram API with Instagram Login**, а не Facebook Login. У Meta App Dashboard відкрийте `Instagram → API setup with Instagram login → Set up Instagram business login` і додайте точний OAuth Redirect URI:

```text
https://bona-doors.com.ua/admin/instagram/callback
```

Для читання власної стрічки достатньо `instagram_business_basic`. Значення `INSTAGRAM_APP_ID` та `INSTAGRAM_APP_SECRET` беруться з Instagram Business Login settings. Токен зберігається в базі зашифрованим; команда `instagram:refresh-token`, яку запускає Laravel Scheduler, автоматично продовжує його до закінчення 60-денного строку.

## GitHub

Створити protected Environment з назвою `production` і додати secrets:

- `PRODUCTION_SSH_HOST`;
- `PRODUCTION_SSH_USER`;
- `PRODUCTION_SSH_PRIVATE_KEY`;
- `PRODUCTION_SSH_KNOWN_HOSTS` — заздалегідь перевірений host key, не результат `ssh-keyscan` під час deploy;
- `PRODUCTION_DEPLOY_PATH`, наприклад `/var/www/bona`;
- `PRODUCTION_PHP_BIN`, наприклад `/usr/bin/php8.4`;
- `PRODUCTION_HEALTHCHECK_URL`, наприклад `https://example.com/up`.

У `.github/workflows/release.yml` деплой запускається тільки вручну з `deploy=true`. Push тега `release-*` лише перевіряє та збирає архів, але не деплоїть його.

## Перший реліз

1. Зробити окремий backup бази й `storage`.
2. На staging-копії production бази виміряти час міграції money-колонок у `2026_08_29_080000_convert_money_columns_to_decimal.php`. На великих таблицях вона може взяти lock.
3. У GitHub Actions вручну запустити `Release` з `deploy=false` і переконатися, що verify/package зелені.
4. У погоджене deploy-вікно запустити workflow з `deploy=true` і підтвердити protected Environment.
5. Перевірити `/up`, головну, пошук, кошик, checkout, login, admin і queue worker.

## Rollback

Скрипт rollback перемикає код на попередній реліз і перезапускає довгоживучі процеси:

```bash
PHP_BIN=/usr/bin/php8.4 bash /var/www/bona/current/deploy/rollback.sh \
  /var/www/bona previous https://example.com/up
```

Rollback **не відкочує міграції бази**. Міграції релізу мають бути backward-compatible; для аварійного відновлення БД використовується backup. Старі релізи не видаляються автоматично: після успішної перевірки залишайте щонайменше 3–5 останніх.
