# Google Analytics 4 — події та налаштування

Ресурс: **G-0863474309**. Тег завантажується для всіх відвідувачів у режимі **Google Consent Mode v2 (advanced)**: без згоди — без cookies та ідентифікаторів, після «Дозволити всі» — повноцінно.

## Події, які надсилає сайт

| Подія | Коли | Звідки дані |
|---|---|---|
| `page_view` | кожна сторінка | автоматично (gtag) |
| `view_item` | сторінка товару | сервер: `GoogleAnalyticsCommerce::viewItem` |
| `add_to_cart` | товар з'явився в кошику або збільшилась кількість: сторінка товару, «Купити в кредит», комплекти, конфігуратор, кошик | різниця кошика до/після (`analytics.js`) |
| `remove_from_cart` | видалення рядка або зменшення кількості | різниця кошика до/після |
| `view_cart` | відкриття сторінки кошика або ручне відкриття бічного кошика | поточний кошик |
| `begin_checkout` | сторінка оформлення | сервер: `GoogleAnalyticsCommerce::beginCheckout` |
| `add_shipping_info` | відправка форми оформлення (`shipping_tier`) | дані `begin_checkout` + обрана доставка |
| `add_payment_info` | відправка форми оформлення (`payment_type`) | дані `begin_checkout` + обрана оплата |
| `purchase` | сторінка «Дякуємо за замовлення» (один раз на замовлення) | сервер: `GoogleAnalyticsCommerce::purchase` |
| `generate_lead` | успішна відправка будь-якої форми заявки (`lead_source` = назва форми) | `cookie-consent.js` |
| `submit_form_*` | ті самі форми, історичні назви — залишені для сумісності зі старими звітами | форми |

**Формат товару** (однаковий у всіх подіях): `item_id` (артикул або slug), `item_name` (завжди українською), `item_brand`, `item_category` (тип товару), `item_variant` (колір та опції), `price` (ціна з опціями), `quantity`. Валюта — `UAH`.

**Значення для оплати** (`payment_type`): `cash`, `card_liqpay`, `installments_privatbank`, `installments_monobank`, `invoice`, `manager_confirmation`.
**Значення для доставки** (`shipping_tier`): `courier_odesa`, `nova_poshta`, `meest`, `store_pickup`, `sat`.

`purchase.transaction_id` має формат `BD-000123` (як номер замовлення на сторінці подяки). `value` — сума товарів після знижки без доставки, `shipping` — доставка окремо.

Події надсилаються для всіх відвідувачів незалежно від вибору в банері. Повторне відкриття сторінки подяки не дублює `purchase`.

## Що налаштувати в адмінці GA4 (один раз)

> Стан на 17.09.2026 (аудит Cowork): ключовими позначені лише `purchase`, `close_convert_lead`, `qualify_lead` за замовчуванням; зберігання подій 2 місяці; unwanted referrals порожній; фільтр внутрішнього трафіку в стані «Тестування» без правил; Search Console і Google Ads не прив'язані; Enhanced measurement «Взаємодії з формою» увімкнено.

1. **Admin → Events → Key events.** Позначити ключовими:
   - `purchase` (уже позначена)
   - `generate_lead`

   Зняти позначку з `close_convert_lead` і `qualify_lead`: сайт їх не надсилає. `submit_form_*` ключовими не позначати, інакше заявки рахуватимуться двічі.

2. **Admin → Data streams → потік сайту → Configure tag settings → List unwanted referrals.** Додати домени платіжних сервісів, щоб замовлення не приписувались їм як джерелу трафіку:
   - `liqpay.ua` (у проєкті використовується `www.liqpay.ua`)
   - `privatbank.ua` (у проєкті використовується `payparts2.privatbank.ua`)
   - `monobank.com.ua` (у проєкті використовується `u2.monobank.com.ua`)

   Правило «contains» покриває піддомени. Остаточний список звірити зі звітом Traffic acquisition → Session source/medium: усі рядки `…/referral` з назвами банків і платіжних сервісів мають потрапити сюди.

3. **Admin → Custom definitions → Create custom dimension** (область «Event»):
   - `lead_source` — щоб бачити, з якої форми прийшла заявка.
   - `payment_type` і `shipping_tier` для `purchase` доступні в стандартних e-commerce звітах, окремі виміри не потрібні.

4. **Admin → Data settings → Data retention:** встановити 14 місяців (за замовчуванням 2 місяці — занадто мало для порівняння сезонів).

5. **Admin → Data display → Reporting identity:** обрати **Blended**. Без цього GA4 не показуватиме змодельовані дані відвідувачів, які не погодились на cookies.

6. **Внутрішній трафік.** Admin → Data streams → потік → Configure tag settings → **Define internal traffic**: додати правила з IP салонів, офісу та розробників (у звіті помітна частка трафіку з Німеччини: Bielefeld, Bonn, Wuppertal — перевірити, чи це свої). Потім Admin → **Data filters** → «Internal Traffic» перевести зі стану «Тестування» в **«Активний»**.

   Локальні та тестові копії сайту більше не надсилають дані в цей ресурс: ID підставляється лише при `APP_ENV=production`.

7. **Admin → Product links → Search Console links:** прив'язати Search Console (зараз не прив'язано) — без цього не видно пошукових запитів.

8. **Admin → Data streams → потік → Enhanced measurement → «Взаємодії з формою»: вимкнути.** Вони дублюють `generate_lead` / `submit_form_*` і рахують інакше (form_start 6, form_submit 1 проти 2 реальних заявок).

9. **Admin → Data streams → потік → Redact data → «Параметри запиту URL»: увімкнути** та додати `signature`, `expires`, `token`. Сайт уже прибирає ці параметри з `page_location`, це додатковий захист на боці Google.

## Як перевірити, що події приходять

1. Відкрити сайт з параметром `?gtm_debug=x` або ввімкнути розширення **Google Analytics Debugger**.
2. Для перевірки повних подій натиснути «Дозволити всі». Без згоди запити до GA теж ідуть, але з параметром `gcs=G100` (сховище заборонене).
3. **Admin → DebugView:** пройти шлях товар → додати в кошик → кошик → оформлення → замовлення (можна тестове, з оплатою «за рахунком»).
4. Кожна подія має містити `items` з назвою та ціною; `purchase` — `transaction_id` і `value`.
5. Звіти **Monetization → E-commerce purchases** і воронка **Checkout journey** почнуть заповнюватись через 24–48 годин.

## Згода на cookies (Consent Mode)

Сайт працює в режимі **advanced**:

- **Без вибору або після «Лише необхідні».** Тег Google завантажується, але `analytics_storage`, `ad_storage`, `ad_user_data` і `ad_personalization` мають стан `denied`. GA4 не ставить cookies і отримує знеособлені сигнали (cookieless pings) про перегляди, додавання в кошик, заявки та покупки.
- **Після «Дозволити всі».** Стан змінюється на `granted`, GA4 працює з cookies. Вибір запам'ятовується, і на наступних сторінках згода відновлюється ще до першого перегляду.
- **Відкликання згоди** («Лише необхідні» після «Дозволити всі») змінює стан на `denied` і видаляє cookies `_ga`, `_ga_*`, `_gid`.

**Чого очікувати у звітах.** Відвідувачі без згоди не з'являються як окремі користувачі. GA4 **моделює** їхню поведінку та конверсії (behavioral і conversion modeling), якщо ресурс відповідає порогам Google: зокрема, щонайменше 1 000 подій на день від відвідувачів без згоди протягом 7 днів. Щоб бачити змодельовані дані, увімкніть Reporting identity → **Blended** (див. вище).

**Юридична частина.** Текст банера оновлено й описує цей режим. Текст сторінки «Політика конфіденційності» редагується в адмінці: у ньому теж треба описати, що Google Analytics без згоди працює без cookies та ідентифікаторів.
