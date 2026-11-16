# Підтвердження заявок SERP Agent

Сервер звітує лише після прийняття й збереження заявки/замовлення. Звіт виконується через Laravel `dispatchAfterResponse`; окремий worker не потрібен. Відповідь клієнту відправляється перед завершальним обробником (FastCGI/LiteSpeed).

| Обробник UA та RU | `formId` |
| --- | --- |
| `/user-choose-doors` — замір | `measure` |
| `/user-choose-doors` — персональна добірка / чинне вікно консультації | `selection` — значення `data-lead-form` |
| `/user-choose-doors` — стара консультація без ідентифікатора | `consultation` |
| `/product/{slug}/one-click` | `one-click` |
| `/checkout/confirm` | `checkout` |
| `/order-count-doors` — `#order-request-form` | `order-request` |

Окремої активної форми запису в шоурум і окремого маршруту зараз немає; залишився лише модуль маски часу. Спільний обробник заявок підтримує `form_id=showroom-visit`; обидві мовні адреси покриті тестами. Відгуки, вхід, реєстрація, вихід, відновлення пароля й пошук не викликають сервіс підтвердження.

`#order-request-form` збережена: у чинному коді її відкриває «Залишити заявку» для товарів під замовлення/без доступної покупки. Форма отримала справжній локалізований `action`, приховані дані товару/сторінки та передачу `sa_event`. Обробник уже зберігає `VisitRequest` у розділі «Заявки» адмінки й надсилає Telegram та налаштовані email-сповіщення. Зовнішнього CRM-конектора для цих форм у репозиторії немає; нового не додавали. При невдалому збереженні повертається помилка, без хибного підтвердження SERP.

## Протокол і конфігурація

- Єдиний endpoint: `https://app.serp-agent.com/api/conversion-events/verify`.
- Рівно п’ять полів: `projectId`, `eventKey`, `formPlugin`, `formId`, `pageUrl`.
- `eventKey` — `sa_event` від трекера, або `null`, якщо його немає/він некоректний. Власні `externalId` не створюються.
- `pageUrl` — `source_url`, за відсутності — `Referer`. Дозволений лише точний домен `bona-doors.com.ua` через HTTP(S), без credentials/нестандартного порту. Query та fragment прибираються, щоб не передавати контактні дані чи секрети з URL. Стороння адреса дає `null`.
- JSON серіалізується один раз; підпис і відправлення використовують ті самі байти. `X-SerpAgent-Signature` = base64url HMAC-SHA256 від `timestamp.rawBody`; `X-SerpAgent-Timestamp` — Unix seconds.
- Використовується наявний `SERP_AGENT_WEBHOOK_SECRET`, той самий, що для `/api/serp-agent/articles`. Нового секрету не потрібно. `SERP_AGENT_API_KEY` і `SERP_AGENT_BASE_URL` більше не використовуються для заявок.
- Project ID за замовчуванням `cmt2pz3ou0004b0i29eg5zca1`; необов’язкове перевизначення `SERP_AGENT_PROJECT_ID`.
- У production звіт увімкнений за замовчуванням. Локальні/staging-копії не звітують без явного `SERP_AGENT_CONVERSIONS_ENABLED=true`. Тести підміняють HTTP і секрет.
- Timeout і connect timeout — 3 с; максимум три спроби лише для мережевих помилок та 5xx. 403/429/redirect не повторюються. Redirect не виконується. `200 verified` та `200 duplicate` визнаються успіхом.
- У логах лише загальне повідомлення/HTTP-статус, без секрету, підпису, тіла запиту/відповіді чи персональних даних.
- Старий `/v1/.../conversions` прибраний із one-click і checkout. Старий клас job залишений як no-op для сумісності з уже наявними повідомленнями черги; він нічого не надсилає.

## Перевірки

```text
HMAC test vector: mVcfEnFwMH78blIYm2FNHRGjSzTVjkM5-3DJIvYASRY
MATCH; no network request
PHPUnit: OK (514 tests, 17036 assertions)
JavaScript: 58 passed, 0 failed
Vite production build: passed
Pint (changed PHP files): passed
```

`tests/Feature/SerpAgentConversionTest.php` перевіряє обидві мови всіх обробників, збереження до звіту, Telegram для заявок, рівно один HTTP-запит, п’ять полів, заголовки, незалежну перевірку HMAC, відсутність контактних даних, відсутність старого endpoint, відповіді duplicate/403/429/redirect/500, timeout, невдале збереження, after-response, вимкнений звіт і негативні сценарії для відгуків/входу/реєстрації.

```sh
php artisan test --filter=SerpAgentConversionTest
php artisan serp-agent:diagnose
```

`serp-agent:diagnose` лише читає конфігурацію й показує READY/INACTIVE без значень секретів та без мережевих запитів. Реліз запускає цю перевірку після оновлення кешу.

Живі заявки для перевірки не надсилалися. Після релізу власник надсилає одну справжню заявку; SERP Agent має показати «підтверджено сайтом» і зіставити її з браузерною подією за `sa_event`. Це фінальна перевірка доставки на стороні SERP Agent; локальні тести її не підміняють.
