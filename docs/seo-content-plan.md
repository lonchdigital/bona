# План SEO-контенту Bona Doors

Документ для наповнення сайту текстами через окремий API. Складено за результатами аудиту сайту та Google Search Console 16–17 вересня 2026 року.

Мета — прибрати причини, через які Google не індексує сторінки («Проскановано/Виявлено – наразі не проіндексовано», «Копія»), і створити посадкові сторінки під пошукові запити.

## Зміст

1. [Як завантажувати контент товарів](#1-як-завантажувати-контент-товарів)
2. [Пріоритети](#2-пріоритети)
3. [Товари: що наповнити](#3-товари-що-наповнити)
4. [Сторінки, які треба створити або доповнити](#4-сторінки-які-треба-створити-або-доповнити)
5. [Російська версія](#5-російська-версія)
6. [Що потребує доробки коду перед наповненням](#6-що-потребує-доробки-коду-перед-наповненням)
7. [Правила написання](#7-правила-написання)
8. [Що вже зроблено](#8-що-вже-зроблено)
9. [Конфігуратор — наступний етап](#9-конфігуратор--наступний-етап)

---

## 1. Як завантажувати контент товарів

Для **товарів і категорій** імпорт уже готовий. Для посадкових сторінок груп фільтрів і послуг його поки немає (див. [розділ 6](#6-що-потребує-доробки-коду-перед-наповненням)).

**Кроки:**

1. API генерує JSON-файл і кладе його в `database/content/products/`, наприклад `2026_10_01_korfad_doors.json`.
2. Створюється міграція, яка викликає імпорт (приклад: `database/migrations/2026_09_17_100000_fill_dnd_handle_content.php`), і запускається деплой. Або вручну на сервері:
   ```bash
   php artisan products:apply-content 2026_10_01_korfad_doors.json
   ```
3. Команда повідомляє, скільки товарів оновлено і які slug не знайдено.

**Формат запису** (один об'єкт на товар, обидві мови обов'язкові):

```json
[
  {
    "slug": "mizhkimnatni-dveri-porto-deluxe-pd-01-korfad",
    "replace_content": true,
    "short_content": { "uk": "<p>Одне речення.</p>", "ru": "<p>Одно предложение.</p>" },
    "content": { "uk": "<h2>…</h2><p>…</p><h3>…</h3><ul><li>…</li></ul>", "ru": "…" },
    "characteristics": [
      { "name": { "uk": "Товщина полотна", "ru": "Толщина полотна" }, "value": { "uk": "40 мм", "ru": "40 мм" } }
    ],
    "faqs": [
      { "question": { "uk": "…?", "ru": "…?" }, "answer": { "uk": "…", "ru": "…" } }
    ],
    "meta_title": { "uk": "… — купити в Одесі | Bona Doors", "ru": "… — купить в Одессе | Bona Doors" },
    "meta_description": { "uk": "…", "ru": "…" }
  }
]
```

**Як поводиться імпорт** (він безпечний для повторного запуску):

| Поле | Коли записується |
|---|---|
| `content` | якщо поточний опис порожній або коротший за 40 слів; **або** якщо в записі стоїть `"replace_content": true` |
| `short_content` | тільки якщо порожній |
| `characteristics` | додаються лише ті, чиїх назв у товару ще немає |
| `faqs` | тільки якщо в товару немає жодного FAQ |
| `meta_title`, `meta_description` | тільки якщо порожні |

> ⚠️ **`replace_content: true`** обов'язковий для груп із шаблонним описом довшим за 40 слів — насамперед **Korfad**. Без прапорця імпорт вважатиме опис заповненим і пропустить його.
>
> ⚠️ **Мета-теги не перезаписуються.** Якщо в товару вже вписано `meta_title` (наприклад, однаковий для 23 плінтусів), імпорт його не змінить. Для таких груп потрібен прапорець `replace_meta` — див. [розділ 6](#6-що-потребує-доробки-коду-перед-наповненням).

Приклади готових файлів: `database/content/products/2026_09_17_hidden_doors.json`, `database/content/products/2026_09_17_dnd_handles.json`.

Для категорій JSON-файли зберігаються в `database/content/categories/`, а ручний запуск виконується командою:

```bash
php artisan categories:apply-content 2026_09_22_accessory_categories.json
```

Імпортер записує `seo_title`, `seo_text`, `meta_title` і `meta_description` у ті самі поля, які редагуються в адмінці. За замовчуванням він заповнює лише порожні значення та не стирає правки менеджера.

---

## 2. Пріоритети

| # | Група | Кількість | Проблема в Google | Тип роботи |
|---|---|---|---|---|
| 1 | Двері **Korfad** | 64 товари | Однаковий шаблонний опис, «Проскановано/Виявлено – не проіндексовано» | Унікальні описи, `replace_content: true` |
| 2 | Ручки **МВМ** | 273 товари | Немає ні опису, ні характеристик; RU-версії «Виявлено – не проіндексовано» | Описи + характеристики |
| 3 | **Посадкові сторінки стилів і кольорів** | 4 стилі + 5–6 кольорів | Сторінки стилів Google склеює з каталогом; кольоровим бракує тексту | Спершу доробка коду, потім тексти |
| 4 | **Комплектуючі** (короби, лиштва, добори, завіси…) | 94 товари | Порожній або однорядковий опис | Описи + характеристики |
| 5 | **Однакові title** (плінтуси, технічні та вхідні двері, погонаж Estet) | 61 товар у 8 групах | «Копія», канібалізація | Унікальні meta; потрібен `replace_meta` |
| 6 | **Розсувні двері** | 20 товарів | Немає ціни й опису | Описи + характеристики; ціни в адмінці |
| 7 | Інші міжкімнатні двері з коротким описом | 55 товарів | Тонкий контент | Розширити опис |
| 8 | Категорії аксесуарів, послуги | ~10 сторінок | Тонкий контент | Тексти в адмінці |
| 9 | **Російська версія** | 20 статей блогу + 16 товарів + довідники | Українські тексти на /ru/, немає RU-статей | RU-статті через API, довідники вручну ([розділ 5](#5-російська-версія)) |

---

## 3. Товари: що наповнити

### 3.1. Двері Korfad — 64 моделі (пріоритет 1)

Усі моделі мають однаковий опис: «Виробник міжкімнатних дверей ТМ Korfad, м. Корюківка… Розмір дверних полотен 2000×600/700/800/900 мм. Товщина полотна 40 мм. Термін виготовлення 10–25 робочих днів. Двері покриваються плівкою Sincrolam або екошпоном…». Для Google це 64 копії однієї сторінки.

**Потрібно для кожної моделі:**
- унікальний опис 200–300 слів: колекція (Porto, Porto Deluxe, Venecia Deluxe, Classico, Aluminium Loft Plato, Glass Loft Plato, Wood Plato, Deco Loft Plato, Piano Deluxe, Parma, Scalea, Florence, Valentino Deluxe тощо), конструкція, скло або молдинги конкретної моделі, покриття, кольори, до яких інтер'єрів пасує;
- характеристики: розміри, товщина полотна, покриття, скло (якщо є), термін виготовлення, доступні кольори;
- 3–4 FAQ;
- у записі `"replace_content": true`.

**Факти, які вже підтверджені** (зі спільного опису на сайті): розміри 2000×600/700/800/900 мм, товщина 40 мм, термін виготовлення 10–25 робочих днів, покриття Sincrolam або екошпон. Решту брати з офіційного сайту Korfad.

<details>
<summary>Список slug (64)</summary>

- `mizhkimnatni-dveri-aliano-al-01-korfad`
- `mizhkimnatni-dveri-aliano-al-02-korfad`
- `mizhkimnatni-dveri-aliano-al-03-korfad`
- `mizhkimnatni-dveri-aliano-al-04-korfad`
- `mizhkimnatni-dveri-aliano-al-05-korfad`
- `mizhkimnatni-dveri-aliano-al-06-korfad`
- `mizhkimnatni-dveri-aliano-al-07-korfad`
- `mizhkimnatni-dveri-aluminium-loft-plato-alp-01-korfad`
- `mizhkimnatni-dveri-aluminium-loft-plato-alp-02-korfad`
- `mizhkimnatni-dveri-aluminium-loft-plato-alp-03-korfad`
- `mizhkimnatni-dveri-aluminium-loft-plato-alp-07-korfad`
- `mizhkimnatni-dveri-classico-cl-02-korfad`
- `mizhkimnatni-dveri-classico-cl-05-korfad`
- `mizhkimnatni-dveri-classico-cl-07-korfad`
- `mizhkimnatni-dveri-classico-cl-08-korfad`
- `mizhkimnatni-dveri-classico-cl-09-korfad`
- `mizhkimnatni-dveri-deco-loft-plato-dlp-01-korfad`
- `mizhkimnatni-dveri-florence-fl-01-korfad`
- `mizhkimnatni-dveri-florence-fl-02-korfad`
- `mizhkimnatni-dveri-florence-fl-03-korfad`
- `mizhkimnatni-dveri-florence-fl-04-korfad`
- `mizhkimnatni-dveri-florence-fl-05-korfad`
- `mizhkimnatni-dveri-glass-loft-plato-glp-01-korfad`
- `mizhkimnatni-dveri-glass-loft-plato-glp-02-korfad`
- `mizhkimnatni-dveri-korfad-exellence-animas`
- `mizhkimnatni-dveri-korfad-exellence-asati`
- `mizhkimnatni-dveri-korfad-exellence-atlant`
- `mizhkimnatni-dveri-korfad-exellence-bonetti`
- `mizhkimnatni-dveri-korfad-exellence-brillar`
- `mizhkimnatni-dveri-korfad-exellence-calypso`
- `mizhkimnatni-dveri-korfad-exellence-celestia`
- `mizhkimnatni-dveri-korfad-exellence-infinity`
- `mizhkimnatni-dveri-korfad-exellence-marion`
- `mizhkimnatni-dveri-korfad-exellence-monarch`
- `mizhkimnatni-dveri-korfad-exellence-monarch-glass`
- `mizhkimnatni-dveri-korfad-exellence-paulina`
- `mizhkimnatni-dveri-korfad-exellence-quantum`
- `mizhkimnatni-dveri-korfad-exellence-ramira`
- `mizhkimnatni-dveri-korfad-exellence-roisel`
- `mizhkimnatni-dveri-korfad-exellence-salin`
- `mizhkimnatni-dveri-korfad-exellence-sparta`
- `mizhkimnatni-dveri-korfad-exellence-weston`
- `mizhkimnatni-dveri-korfad-exellence-wetter`
- `mizhkimnatni-dveri-loft-plato-lp-01-korfad`
- `mizhkimnatni-dveri-milano-ml-05-korfad`
- `mizhkimnatni-dveri-parma-pm-10-korfad`
- `mizhkimnatni-dveri-piano-deluxe-pnd-01-korfad`
- `mizhkimnatni-dveri-porto-deluxe-pd-01-korfad`
- `mizhkimnatni-dveri-porto-deluxe-pd-03-korfad`
- `mizhkimnatni-dveri-porto-pr-01-korfad`
- `mizhkimnatni-dveri-porto-pr-05-korfad`
- `mizhkimnatni-dveri-porto-pr-08-korfad`
- `mizhkimnatni-dveri-porto-pr-10-korfad`
- `mizhkimnatni-dveri-porto-pr-12-korfad`
- `mizhkimnatni-dveri-sanremo-sr-01-korfad`
- `mizhkimnatni-dveri-sanvito-dzerkalo-sv-01-korfad`
- `mizhkimnatni-dveri-sanvito-sv-01-korfad`
- `mizhkimnatni-dveri-scalea-sc-04-korfad`
- `mizhkimnatni-dveri-valentino-deluxe-vld-01-korfad`
- `mizhkimnatni-dveri-valentino-deluxe-vld-03-korfad`
- `mizhkimnatni-dveri-venecia-deluxe-vnd-02-korfad`
- `mizhkimnatni-dveri-venecia-deluxe-vnd-04-korfad`
- `mizhkimnatni-dveri-venecia-deluxe-vnd-05-korfad`
- `mizhkimnatni-dveri-wood-plato-wp-01-korfad`

</details>


### 3.2. Ручки МВМ — 273 товари (пріоритет 2)

Немає ні опису, ні характеристик. Кожна модель продається у варіантах: базова ручка на розетці, з накладкою під циліндр, з накладкою під WC, у кількох покриттях.

**Моделі:** A-1209 (3), A-1220 (3), A-1355 (3), A-2003 (3), A-2004 (12), A-2005 (6), A-2006 (6), A-2008 (6), A-2010/E20 (9), A-2015/E20 (12), A-2017 (3), A-2018 (12), A-2020 (9), A-2021 (9), A-2022 (6), A-2024 (6), A-2025 (6), A-2028 (6), A-2029 (6), A-2030 (6), Z-1210 (3), Z-1220 (3), Z-1220/E20 (6), Z-1259 (6), Z-1290 (3), Z-1311 (3), Z-1312 (6), Z-1319 (3), Z-1320/E20 (12), Z-1325 (6), Z-1355 (3), Z-1420 (6), Z-1807 (6), Z-1808 (6), Z-1809 (6), Z-1809/E20 (9), Z-1810 (15), Z-1811 (6), Z-1811/E20 (9), Z-1812 (6), Z-1813 (9), Z-1814 (9).

**Потрібно:**
- опис 150–250 слів; абзац про модель може повторюватися між варіантами, а абзаци про покриття і тип накладки мають відрізнятися;
- характеристики: бренд, модель, тип (ручка на розетці / з накладкою під циліндр / під WC), покриття; матеріал і форма розетки — лише якщо підтверджені на сайті МВМ;
- 3 FAQ, специфічні для варіанта (чи потрібен окремо циліндр, чи підходить для ванної тощо);
- розшифрування кодів покриттів: SN/CP — матовий нікель / полірований хром, BN/SBN — чорний нікель / матовий чорний нікель, MC — матовий хром, SC — сатин хром, MN — сатин нікель, AB — стара бронза, MA — матовий антрацит, MACC — матова бронза. Код E20 потребує підтвердження.

Зразок готового формату для ручок: `database/content/products/2026_09_17_dnd_handles.json`.

<details>
<summary>Список slug (268; ще 5 варіантів MVM наведені в переліку комплектуючих у розділі 3.3)</summary>

- `z-1210-sn-cp-matoviy-nikel-polirovaniy-hrom` — Z-1210 SN/CP, матовий нікель/полірований хром
- `z-1210-sn-cp-z-nakladkoyu-pid-cilindr-matoviy-nikel-polirovaniy-hrom` — Z-1210 SN/CP з накладкою під циліндр, матовий нікель/полірований хром
- `z-1210-sn-cp-z-nakladkoyu-pid-wc-matoviy-nikel-polirovaniy-hrom` — Z-1210 SN/CP з накладкою під WC, матовий нікель/полірований хром
- `z-1220-e20-chorniy` — Z-1220/E20, чорний
- `z-1220-e20-z-nakladkoyu-pid-cilindr-chorniy` — Z-1220/E20 з накладкою під циліндр, чорний
- `z-1220-e20-z-nakladkoyu-pid-wc-chorniy` — Z-1220/E20 з накладкою під WC, чорний
- `z-1220-e20-sn-matoviy-nikel` — Z-1220/E20 SN, матовий нікель
- `z-1220-e20-sn-z-nakladkoyu-pid-cilindr-matoviy-nikel` — Z-1220/E20 SN з накладкою під циліндр, матовий нікель
- `z-1220-e20-sn-z-nakladkoyu-pid-wc-matoviy-nikel` — Z-1220/E20 SN з накладкою під WC, матовий нікель
- `z-1220-sn-cp-matoviy-nikel-polirovaniy-hrom` — Z-1220 SN/CP, матовий нікель/полірований хром
- `z-1220-sn-cp-z-nakladkoyu-pid-cilindr-matoviy-nikel-polirovaniy-hrom` — Z-1220 SN/CP з накладкою під циліндр, матовий нікель/полірований хром
- `z-1220-sn-cp-z-nakladkoyu-pid-wc-matoviy-nikel-polirovaniy-hrom` — Z-1220 SN/CP з накладкою під WC, матовий нікель/полірований хром
- `z-1259-ab-stara-bronza` — Z-1259 AB, стара бронза
- `z-1259-ab-z-nakladkoyu-pid-cilindr-stara-bronza` — Z-1259 AB з накладкою під циліндр, стара бронза
- `z-1259-ab-z-nakladkoyu-pid-wc-stara-bronza` — Z-1259 AB з накладкою під WC, стара бронза
- `z-1259-sn-cp-matoviy-nikel-polirovaniy-hrom` — Z-1259 SN/CP, матовий нікель/полірований хром
- `z-1259-sn-cp-z-nakladkoyu-pid-cilindr-matoviy-nikel-polirovaniy-hrom` — Z-1259 SN/CP з накладкою під циліндр, матовий нікель/полірований хром
- `z-1259-sn-cp-z-nakladkoyu-pid-wc-matoviy-nikel-polirovaniy-hrom` — Z-1259 SN/CP з накладкою під WC, матовий нікель/полірований хром
- `z-1290-sn-cp-matoviy-nikel-polirovaniy-hrom` — Z-1290 SN/CP, матовий нікель/полірований хром
- `z-1290-sn-cp-z-nakladkoyu-pid-cilindr-matoviy-nikel-polirovaniy-hrom` — Z-1290 SN/CP з накладкою під циліндр, матовий нікель/полірований хром
- `z-1290-sn-cp-z-nakladkoyu-pid-wc-matoviy-nikel-polirovaniy-hrom` — Z-1290 SN/CP з накладкою під WC, матовий нікель/полірований хром
- `z-1311-macc-matova-bronza` — Z-1311 MACC, матова бронза
- `z-1311-macc-z-nakladkoyu-pid-cilindr-matova-bronza` — Z-1311 MACC з накладкою під циліндр, матова бронза
- `z-1311-macc-z-nakladkoyu-pid-wc-matova-bronza` — Z-1311 MACC з накладкою під WC, матова бронза
- `z-1312-ab-stara-bronza` — Z-1312 AB, стара бронза
- `z-1312-ab-z-nakladkoyu-pid-cilindr-stara-bronza` — Z-1312 AB з накладкою під циліндр, стара бронза
- `z-1312-ab-z-nakladkoyu-pid-wc-stara-bronza` — Z-1312 AB з накладкою під WC, стара бронза
- `z-1312-macc-matova-bronza` — Z-1312 MACC, матова бронза
- `z-1312-macc-z-nakladkoyu-pid-cilindr-matova-bronza` — Z-1312 MACC з накладкою під циліндр, матова бронза
- `z-1312-macc-z-nakladkoyu-pid-wc-matova-bronza` — Z-1312 MACC з накладкою під WC, матова бронза
- `z-1319-sn-cp-matoviy-nikel-polirovaniy-hrom` — Z-1319 SN/CP, матовий нікель/полірований хром
- `z-1319-sn-cp-z-nakladkoyu-pid-cilindr-matoviy-nikel-polirovaniy-hrom` — Z-1319 SN/CP з накладкою під циліндр, матовий нікель/полірований хром
- `z-1320-e20-chorniy` — Z-1320/E20, чорний
- `z-1320-e20-z-nakladkoyu-pid-cilindr-chorniy` — Z-1320/E20 з накладкою під циліндр, чорний
- `z-1320-e20-z-nakladkoyu-pid-wc-chorniy` — Z-1320/E20 з накладкою під WC, чорний
- `z-1320-e20-mc-matoviy-hrom` — Z-1320/E20 MC, матовий хром
- `z-1320-e20-mc-z-nakladkoyu-pid-cilindr-matoviy-hrom` — Z-1320/E20 MC з накладкою під циліндр, матовий хром
- `z-1320-e20-mc-z-nakladkoyu-pid-wc-matoviy-hrom` — Z-1320/E20 MC з накладкою під WC, матовий хром
- `z-1320-e20-bn-sbn-chorniy-nikel-matoviy-chorniy-nikel` — Z-1320/E20 BN/SBN, чорний нікель/матовий чорний нікель
- `z-1320-e20-bn-sbn-z-nakladkoyu-pid-cilindr-chorniy-nikel-matoviy-chorniy-nikel` — Z-1320/E20 BN/SBN з накладкою під циліндр, чорний нікель/матовий чорний нікель
- `z-1320-e20-bn-sbn-z-nakladkoyu-pid-wc-chorniy-nikel-matoviy-chorniy-nikel` — Z-1320/E20 BN/SBN з накладкою під WC, чорний нікель/матовий чорний нікель
- `z-1320-e20-sc-satin-hrom` — Z-1320/E20 SC, сатин хром
- `z-1320-e20-sc-z-nakladkoyu-pid-cilindr-satin-hrom` — Z-1320/E20 SC з накладкою під циліндр, сатин хром
- `z-1320-e20-sc-z-nakladkoyu-pid-wc-satin-hrom` — Z-1320/E20 SC з накладкою під WC, сатин хром
- `z-1325-bn-sbn-chorniy-nikel-matoviy-chorniy-nikel` — Z-1325 BN/SBN, чорний нікель/матовий чорний нікель
- `z-1325-bn-sbn-z-nakladkoyu-pid-cilindr-chorniy-nikel-matoviy-chorniy-nikel` — Z-1325 BN/SBN з накладкою під циліндр, чорний нікель/матовий чорний нікель
- `z-1325-bn-sbn-z-nakladkoyu-pid-wc-chorniy-nikel-matoviy-chorniy-nikel` — Z-1325 BN/SBN з накладкою під WC, чорний нікель/матовий чорний нікель
- `z-1325-sn-cp-matoviy-nikel-polirovaniy-hrom` — Z-1325 SN/CP, матовий нікель/полірований хром
- `z-1325-sn-cp-z-nakladkoyu-pid-cilindr-matoviy-nikel-polirovaniy-hrom` — Z-1325 SN/CP з накладкою під циліндр, матовий нікель/полірований хром
- `z-1325-sn-cp-z-nakladkoyu-pid-wc-matoviy-nikel-polirovaniy-hrom` — Z-1325 SN/CP з накладкою під WC, матовий нікель/полірований хром
- `z-1355-bn-sbn-chorniy-nikel-matoviy-chorniy-nikel` — Z-1355 BN/SBN, чорний нікель/матовий чорний нікель
- `z-1355-bn-sbn-z-nakladkoyu-pid-cilindr-chorniy-nikel-matoviy-chorniy-nikel` — Z-1355 BN/SBN з накладкою під циліндр, чорний нікель/матовий чорний нікель
- `z-1355-bn-sbn-z-nakladkoyu-pid-wc-chorniy-nikel-matoviy-chorniy-nikel` — Z-1355 BN/SBN з накладкою під WC, чорний нікель/матовий чорний нікель
- `z-1420-cp-polirovaniy-hrom` — Z-1420 CP, полірований хром
- `z-1420-cp-z-nakladkoyu-pid-cilindr-polirovaniy-hrom` — Z-1420 CP з накладкою під циліндр, полірований хром
- `z-1420-cp-z-nakladkoyu-pid-wc-polirovaniy-hrom` — Z-1420 CP з накладкою під WC, полірований хром
- `z-1420-mc-matoviy-hrom` — Z-1420 MC, матовий хром
- `z-1420-mc-z-nakladkoyu-pid-cilindr-matoviy-hrom` — Z-1420 MC з накладкою під циліндр, матовий хром
- `z-1420-mc-z-nakladkoyu-pid-wc-matoviy-hrom` — Z-1420 MC з накладкою під WC, матовий хром
- `z-1807-sc-satin-hrom` — Z-1807 SC, сатин хром
- `z-1807-sc-z-nakladkoyu-pid-cilindr-satin-hrom` — Z-1807 SC з накладкою під циліндр, сатин хром
- `z-1807-sc-z-nakladkoyu-pid-wc-satin-hrom` — Z-1807 SC з накладкою під WC, сатин хром
- `z-1807-chorniy` — Z-1807, чорний
- `z-1807-z-nakladkoyu-pid-cilindr-chorniy` — Z-1807 з накладкою під циліндр, чорний
- `z-1807-z-nakladkoyu-pid-wc-chorniy` — Z-1807 з накладкою під WC, чорний
- `z-1808-chorniy` — Z-1808, чорний
- `z-1808-z-nakladkoyu-pid-cilindr-chorniy` — Z-1808 з накладкою під циліндр, чорний
- `z-1808-z-nakladkoyu-pid-wc-chorniy` — Z-1808 з накладкою під WC, чорний
- `z-1808-mn-satin-nikel` — Z-1808 MN, сатин нікель
- `z-1808-mn-z-nakladkoyu-pid-cilindr-satin-nikel` — Z-1808 MN з накладкою під циліндр, сатин нікель
- `z-1808-mn-z-nakladkoyu-pid-wc-satin-nikel` — Z-1808 MN з накладкою під WC, сатин нікель
- `z-1809-mn-satin-nikel` — Z-1809 MN, сатин нікель
- `z-1809-mn-z-nakladkoyu-pid-cilindr-satin-nikel` — Z-1809 MN з накладкою під циліндр, сатин нікель
- `z-1809-mn-z-nakladkoyu-pid-wc-satin-nikel` — Z-1809 MN з накладкою під WC, сатин нікель
- `z-1809-sc-satin-hrom` — Z-1809 SC, сатин хром
- `z-1809-sc-z-nakladkoyu-pid-cilindr-satin-hrom` — Z-1809 SC з накладкою під циліндр, сатин хром
- `z-1809-sc-z-nakladkoyu-pid-wc-satin-hrom` — Z-1809 SC з накладкою під WC, сатин хром
- `z-1809-e20-chorniy` — Z-1809/E20, чорний
- `z-1809-e20-z-nakladkoyu-pid-cilindr-chorniy` — Z-1809/E20 з накладкою під циліндр, чорний
- `z-1809-e20-z-nakladkoyu-pid-wc-chorniy` — Z-1809/E20 з накладкою під WC, чорний
- `z-1809-e20-mn-satin-nikel` — Z-1809/E20 MN, сатин нікель
- `z-1809-e20-mn-z-nakladkoyu-pid-cilindr-satin-nikel` — Z-1809/E20 MN з накладкою під циліндр, сатин нікель
- `z-1809-e20-mn-z-nakladkoyu-pid-wc-satin-nikel` — Z-1809/E20 MN з накладкою під WC, сатин нікель
- `z-1809-e20-sc-satin-hrom` — Z-1809/E20 SC, сатин хром
- `z-1809-e20-sc-z-nakladkoyu-pid-cilindr-satin-hrom` — Z-1809/E20 SC з накладкою під циліндр, сатин хром
- `z-1809-e20-sc-z-nakladkoyu-pid-wc-satin-hrom` — Z-1809/E20 SC з накладкою під WC, сатин хром
- `z-1810-chorniy` — Z-1810, чорний
- `z-1810-z-nakladkoyu-pid-cilindr-chorniy` — Z-1810 з накладкою під циліндр, чорний
- `z-1810-z-nakladkoyu-pid-wc-chorniy` — Z-1810 з накладкою під WC, чорний
- `z-1810-mn-satin-nikel` — Z-1810 MN, сатин нікель
- `z-1810-mn-z-nakladkoyu-pid-cilindr-satin-nikel` — Z-1810 MN з накладкою під циліндр, сатин нікель
- `z-1810-mn-z-nakladkoyu-pid-wc-satin-nikel` — Z-1810 MN з накладкою під WC, сатин нікель
- `z-1810-sc-satin-hrom` — Z-1810 SC, сатин хром
- `z-1810-sc-z-nakladkoyu-pid-cilindr-satin-hrom` — Z-1810 SC з накладкою під циліндр, сатин хром
- `z-1810-sc-z-nakladkoyu-pid-wc-satin-hrom` — Z-1810 SC з накладкою під WC, сатин хром
- `z-1810-ma-matoviy-antracit` — Z-1810 MA, матовий антрацит
- `z-1810-ma-z-nakladkoyu-pid-cilindr-matoviy-antracit` — Z-1810 MA з накладкою під циліндр, матовий антрацит
- `z-1810-ma-z-nakladkoyu-pid-wc-matoviy-antracit` — Z-1810 MA з накладкою під WC, матовий антрацит
- `z-1810-sb-matova-latun` — Z-1810 SB, матова латунь
- `z-1810-sb-z-nakladkoyu-pid-cilindr-matova-latun` — Z-1810 SB з накладкою під циліндр, матова латунь
- `z-1810-sb-z-nakladkoyu-pid-wc-matova-latun` — Z-1810 SB з накладкою під WC, матова латунь
- `z-1811-chorniy` — Z-1811, чорний
- `z-1811-z-nakladkoyu-pid-cilindr-chorniy` — Z-1811 з накладкою під циліндр, чорний
- `z-1811-z-nakladkoyu-pid-wc-chorniy` — Z-1811 з накладкою під WC, чорний
- `z-1811-sc-satin-hrom` — Z-1811 SC, сатин хром
- `z-1811-sc-z-nakladkoyu-pid-cilindr-satin-hrom` — Z-1811 SC з накладкою під циліндр, сатин хром
- `z-1811-sc-z-nakladkoyu-pid-wc-satin-hrom` — Z-1811 SC з накладкою під WC, сатин хром
- `z-1811-e20-chorniy` — Z-1811/E20, чорний
- `z-1811-e20-z-nakladkoyu-pid-cilindr-chorniy` — Z-1811/E20 з накладкою під циліндр, чорний
- `z-1811-e20-z-nakladkoyu-pid-wc-chorniy` — Z-1811/E20 з накладкою під WC, чорний
- `z-1811-e20-mn-satin-nikel` — Z-1811/E20 MN, сатин нікель
- `z-1811-e20-mn-z-nakladkoyu-pid-cilindr-satin-nikel` — Z-1811/E20 MN з накладкою під циліндр, сатин нікель
- `z-1811-e20-mn-z-nakladkoyu-pid-wc-satin-nikel` — Z-1811/E20 MN з накладкою під WC, сатин нікель
- `z-1811-e20-sc-satin-hrom` — Z-1811/E20 SC, сатин хром
- `z-1811-e20-sc-z-nakladkoyu-pid-cilindr-satin-hrom` — Z-1811/E20 SC з накладкою під циліндр, сатин хром
- `z-1811-e20-sc-z-nakladkoyu-pid-wc-satin-hrom` — Z-1811/E20 SC з накладкою під WC, сатин хром
- `a-1209-sn-cp-matoviy-nikel-polirovaniy-hrom` — A-1209 SN/CP, матовий нікель/полірований хром
- `a-1209-sn-cp-z-nakladkoyu-pid-cilindr-matoviy-nikel-polirovaniy-hrom` — A-1209 SN/CP з накладкою під циліндр, матовий нікель/полірований хром
- `a-1209-sn-cp-z-nakladkoyu-pid-wc-matoviy-nikel-polirovaniy-hrom` — A-1209 SN/CP з накладкою під WC, матовий нікель/полірований хром
- `a-1220-sn-cp-matoviy-nikel-polirovaniy-hrom` — A-1220 SN/CP, матовий нікель/полірований хром
- `a-1220-sn-cp-z-nakladkoyu-pid-cilindr-matoviy-nikel-polirovaniy-hrom` — A-1220 SN/CP з накладкою під циліндр, матовий нікель/полірований хром
- `a-1220-sn-cp-z-nakladkoyu-pid-wc-matoviy-nikel-polirovaniy-hrom` — A-1220 SN/CP з накладкою під WC, матовий нікель/полірований хром
- `a-1355-sn-cp-matoviy-nikel-polirovaniy-hrom` — A-1355 SN/CP, матовий нікель/полірований хром
- `a-1355-sn-cp-z-nakladkoyu-pid-cilindr-matoviy-nikel-polirovaniy-hrom` — A-1355 SN/CP з накладкою під циліндр, матовий нікель/полірований хром
- `a-1355-sn-cp-z-nakladkoyu-pid-wc-matoviy-nikel-polirovaniy-hrom` — A-1355 SN/CP з накладкою під WC, матовий нікель/полірований хром
- `a-2003-sn-cp-matoviy-nikel-polirovaniy-hrom` — A-2003 SN/CP, матовий нікель/полірований хром
- `a-2003-sn-cp-z-nakladkoyu-pid-cilindr-matoviy-nikel-polirovaniy-hrom` — A-2003 SN/CP з накладкою під циліндр, матовий нікель/полірований хром
- `a-2003-sn-cp-z-nakladkoyu-pid-wc-matoviy-nikel-polirovaniy-hrom` — A-2003 SN/CP з накладкою під WC, матовий нікель/полірований хром
- `a-2004-ab-stara-bronza` — A-2004 AB, стара бронза
- `a-2004-ab-z-nakladkoyu-pid-cilindr-stara-bronza` — A-2004 AB з накладкою під циліндр, стара бронза
- `a-2004-ab-z-nakladkoyu-pid-wc-stara-bronza` — A-2004 AB з накладкою під WC, стара бронза
- `a-2004-ma-matoviy-antracit` — A-2004 MA, матовий антрацит
- `a-2004-ma-z-nakladkoyu-pid-cilindr-matoviy-antracit` — A-2004 MA з накладкою під циліндр, матовий антрацит
- `a-2004-ma-z-nakladkoyu-pid-wc-matoviy-antracit` — A-2004 MA з накладкою під WC, матовий антрацит
- `a-2004-mc-matoviy-hrom` — A-2004 MC, матовий хром
- `a-2004-mc-z-nakladkoyu-pid-cilindr-matoviy-hrom` — A-2004 MC з накладкою під циліндр, матовий хром
- `a-2004-mc-z-nakladkoyu-pid-wc-matoviy-hrom` — A-2004 MC з накладкою під WC, матовий хром
- `a-2004-sn-cp-matoviy-nikel-polirovaniy-hrom` — A-2004 SN/CP, матовий нікель/полірований хром
- `a-2004-sn-cp-z-nakladkoyu-pid-cilindr-matoviy-nikel-polirovaniy-hrom` — A-2004 SN/CP з накладкою під циліндр, матовий нікель/полірований хром
- `a-2004-sn-cp-z-nakladkoyu-pid-wc-matoviy-nikel-polirovaniy-hrom` — A-2004 SN/CP з накладкою під WC, матовий нікель/полірований хром
- `a-2005-ab-stara-bronza` — A-2005 AB, стара бронза
- `a-2005-ab-z-nakladkoyu-pid-cilindr-stara-bronza` — A-2005 AB з накладкою під циліндр, стара бронза
- `a-2005-ab-z-nakladkoyu-pid-wc-stara-bronza` — A-2005 AB з накладкою під WC, стара бронза
- `a-2005-sn-cp-matoviy-nikel-polirovaniy-hrom` — A-2005 SN/CP, матовий нікель/полірований хром
- `a-2005-sn-cp-z-nakladkoyu-pid-cilindr-matoviy-nikel-polirovaniy-hrom` — A-2005 SN/CP з накладкою під циліндр, матовий нікель/полірований хром
- `a-2005-sn-cp-z-nakladkoyu-pid-wc-matoviy-nikel-polirovaniy-hrom` — A-2005 SN/CP з накладкою під WC, матовий нікель/полірований хром
- `a-2006-ma-matoviy-antracit` — A-2006 MA, матовий антрацит
- `a-2006-ma-z-nakladkoyu-pid-cilindr-matoviy-antracit` — A-2006 MA з накладкою під циліндр, матовий антрацит
- `a-2006-ma-z-nakladkoyu-pid-wc-matoviy-antracit` — A-2006 MA з накладкою під WC, матовий антрацит
- `a-2006-sn-cp-matoviy-nikel-polirovaniy-hrom` — A-2006 SN/CP, матовий нікель/полірований хром
- `a-2006-sn-cp-z-nakladkoyu-pid-cilindr-matoviy-nikel-polirovaniy-hrom` — A-2006 SN/CP з накладкою під циліндр, матовий нікель/полірований хром
- `a-2006-sn-cp-z-nakladkoyu-pid-wc-matoviy-nikel-polirovaniy-hrom` — A-2006 SN/CP з накладкою під WC, матовий нікель/полірований хром
- `a-2008-chorniy` — A-2008, чорний
- `a-2008-z-nakladkoyu-pid-cilindr-chorniy` — A-2008 з накладкою під циліндр, чорний
- `a-2008-z-nakladkoyu-pid-wc-chorniy` — A-2008 з накладкою під WC, чорний
- `a-2008-sn-cp-matoviy-nikel-polirovaniy-hrom` — A-2008 SN/CP, матовий нікель/полірований хром
- `a-2008-sn-cp-z-nakladkoyu-pid-cilindr-matoviy-nikel-polirovaniy-hrom` — A-2008 SN/CP з накладкою під циліндр, матовий нікель/полірований хром
- `a-2008-sn-cp-z-nakladkoyu-pid-wc-matoviy-nikel-polirovaniy-hrom` — A-2008 SN/CP з накладкою під WC, матовий нікель/полірований хром
- `a-2010-e20-chorniy` — A-2010/E20, чорний
- `a-2010-e20-z-nakladkoyu-pid-cilindr-chorniy` — A-2010/E20 з накладкою під циліндр, чорний
- `a-2010-e20-z-nakladkoyu-pid-wc-chorniy` — A-2010/E20 з накладкою під WC, чорний
- `a-2010-e20-ma-matoviy-antracit` — A-2010/E20 MA, матовий антрацит
- `a-2010-e20-ma-z-nakladkoyu-pid-cilindr-matoviy-antracit` — A-2010/E20 MA з накладкою під циліндр, матовий антрацит
- `a-2010-e20-ma-z-nakladkoyu-pid-wc-matoviy-antracit` — A-2010/E20 MA з накладкою під WC, матовий антрацит
- `a-2010-e20-sn-matoviy-nikel` — A-2010/E20 SN, матовий нікель
- `a-2010-e20-sn-z-nakladkoyu-pid-cilindr-matoviy-nikel` — A-2010/E20 SN з накладкою під циліндр, матовий нікель
- `a-2010-e20-sn-z-nakladkoyu-pid-wc-matoviy-nikel` — A-2010/E20 SN з накладкою під WC, матовий нікель
- `a-2015-e20-ma-matoviy-antracit-z-chornoyu-vstavkoyu` — A-2015/E20 MА, матовий антрацит з чорною вставкою
- `a-2015-e20-ma-z-nakladkoyu-pid-cilindr-matoviy-antracit-z-chornoyu-vstavkoyu` — A-2015/E20 MА з накладкою під циліндр, матовий антрацит з чорною вставкою
- `a-2015-e20-ma-z-nakladkoyu-pid-wc-matoviy-antracit-z-chornoyu-vstavkoyu` — A-2015/E20 MА з накладкою під WC, матовий антрацит з чорною вставкою
- `a-2015-e20-ma-matoviy-antracit-z-biloyu-vstavkoyu` — A-2015/E20 MA, матовий антрацит з білою вставкою
- `a-2015-e20-ma-z-nakladkoyu-pid-cilindr-matoviy-antracit-z-biloyu-vstavkoyu` — A-2015/E20 MA з накладкою під циліндр, матовий антрацит з білою вставкою
- `a-2015-e20-ma-z-nakladkoyu-pid-wc-matoviy-antracit-z-biloyu-vstavkoyu` — A-2015/E20 MA з накладкою під WC, матовий антрацит з білою вставкою
- `a-2015-e20-mc-matoviy-hrom-z-chornoyu-vstavkoyu` — A-2015/E20 MC, матовий хром з чорною вставкою
- `a-2015-e20-mc-z-nakladkoyu-pid-cilindr-matoviy-hrom-z-chornoyu-vstavkoyu` — A-2015/E20 MC з накладкою під циліндр, матовий хром з чорною вставкою
- `a-2015-e20-mc-z-nakladkoyu-pid-wc-matoviy-hrom-z-chornoyu-vstavkoyu` — A-2015/E20 MC з накладкою під WC, матовий хром з чорною вставкою
- `a-2015-e20-mc-matoviy-hrom-z-biloyu-vstavkoyu` — A-2015/E20 MC, матовий хром з білою вставкою
- `a-2015-e20-mc-z-nakladkoyu-pid-cilindr-matoviy-hrom-z-biloyu-vstavkoyu` — A-2015/E20 MC з накладкою під циліндр, матовий хром з білою вставкою
- `a-2015-e20-mc-z-nakladkoyu-pid-wc-matoviy-hrom-z-biloyu-vstavkoyu` — A-2015/E20 MC з накладкою під WC, матовий хром з білою вставкою
- `a-2017-chorniy` — A-2017, чорний
- `a-2017-z-nakladkoyu-pid-cilindr-chorniy` — A-2017 з накладкою під циліндр, чорний
- `a-2017-z-nakladkoyu-pid-wc-chorniy` — A-2017 з накладкою під WC, чорний
- `a-2018-chorniy` — A-2018, чорний
- `a-2018-z-nakladkoyu-pid-cilindr-chorniy` — A-2018 з накладкою під циліндр, чорний
- `a-2018-z-nakladkoyu-pid-wc-chorniy` — A-2018 з накладкою під WC, чорний
- `a-2018-mc-matoviy-hrom` — A-2018 MC, матовий хром
- `a-2018-mc-z-nakladkoyu-pid-cilindr-matoviy-hrom` — A-2018 MC з накладкою під циліндр, матовий хром
- `a-2018-mc-z-nakladkoyu-pid-wc-matoviy-hrom` — A-2018 MC з накладкою під WC, матовий хром
- `a-2018-sn-matoviy-nikel` — A-2018 SN, матовий нікель
- `a-2018-sn-z-nakladkoyu-pid-cilindr-matoviy-nikel` — A-2018 SN з накладкою під циліндр, матовий нікель
- `a-2018-sn-z-nakladkoyu-pid-wc-matoviy-nikel` — A-2018 SN з накладкою під WC, матовий нікель
- `a-2018-sc-satin-hrom` — A-2018 SC, сатин хром
- `a-2018-sc-z-nakladkoyu-pid-cilindr-satin-hrom` — A-2018 SC з накладкою під циліндр, сатин хром
- `a-2018-sc-z-nakladkoyu-pid-wc-satin-hrom` — A-2018 SC з накладкою під WC, сатин хром
- `a-2020-chorniy` — A-2020, чорний
- `a-2020-z-nakladkoyu-pid-cilindr-chorniy` — A-2020 з накладкою під циліндр, чорний
- `a-2020-z-nakladkoyu-pid-wc-chorniy` — A-2020 з накладкою під WC, чорний
- `a-2020-ma-matoviy-antracit` — A-2020 MA, матовий антрацит
- `a-2020-ma-z-nakladkoyu-pid-cilindr-matoviy-antracit` — A-2020 MA з накладкою під циліндр, матовий антрацит
- `a-2020-ma-z-nakladkoyu-pid-wc-matoviy-antracit` — A-2020 MA з накладкою під WC, матовий антрацит
- `a-2020-sn-matoviy-nikel` — A-2020 SN, матовий нікель
- `a-2020-sn-z-nakladkoyu-pid-cilindr-matoviy-nikel` — A-2020 SN з накладкою під циліндр, матовий нікель
- `a-2020-sn-z-nakladkoyu-pid-wc-matoviy-nikel` — A-2020 SN з накладкою під WC, матовий нікель
- `a-2021-mc-matoviy-hrom` — A-2021 MC, матовий хром
- `a-2021-mc-z-nakladkoyu-pid-cilindr-matoviy-hrom` — A-2021 MC з накладкою під циліндр, матовий хром
- `a-2021-mc-z-nakladkoyu-pid-wc-matoviy-hrom` — A-2021 MC з накладкою під WC, матовий хром
- `a-2021-chorniy` — A-2021, чорний
- `a-2021-z-nakladkoyu-pid-cilindr-chorniy` — A-2021 з накладкою під циліндр, чорний
- `a-2021-z-nakladkoyu-pid-wc-chorniy` — A-2021 з накладкою під WC, чорний
- `a-2021-sn-matoviy-nikel` — A-2021 SN, матовий нікель
- `a-2021-sn-z-nakladkoyu-pid-cilindr-matoviy-nikel` — A-2021 SN з накладкою під циліндр, матовий нікель
- `a-2021-sn-z-nakladkoyu-pid-wc-matoviy-nikel` — A-2021 SN з накладкою під WC, матовий нікель
- `a-2022-sc-z-nakladkoyu-pid-cilindr-satin-hrom` — A-2022 SC з накладкою під циліндр, сатин хром
- `a-2022-sc-z-nakladkoyu-pid-wc-satin-hrom` — A-2022 SC з накладкою під WC, сатин хром
- `a-2024-chorniy` — A-2024, чорний
- `a-2024-z-nakladkoyu-pid-cilindr-chorniy` — A-2024 з накладкою під циліндр, чорний
- `a-2024-z-nakladkoyu-pid-wc-chorniy` — A-2024 з накладкою під WC, чорний
- `a-2024-sc-satin-hrom` — A-2024 SC, сатин хром
- `a-2024-sc-z-nakladkoyu-pid-cilindr-satin-hrom` — A-2024 SC з накладкою під циліндр, сатин хром
- `a-2024-sc-z-nakladkoyu-pid-wc-satin-hrom` — A-2024 SC з накладкою під WC, сатин хром
- `a-2025-ma-matoviy-antracit` — A-2025 MA, матовий антрацит
- `a-2025-ma-z-nakladkoyu-pid-cilindr-matoviy-antracit` — A-2025 MA з накладкою під циліндр, матовий антрацит
- `a-2025-ma-z-nakladkoyu-pid-wc-matoviy-antracit` — A-2025 MA з накладкою під WC, матовий антрацит
- `a-2025-mn-satin-nikel` — A-2025 MN, сатин нікель
- `a-2025-mn-z-nakladkoyu-pid-cilindr-satin-nikel` — A-2025 MN з накладкою під циліндр, сатин нікель
- `a-2025-mn-z-nakladkoyu-pid-wc-satin-nikel` — A-2025 MN з накладкою під WC, сатин нікель
- `a-2028-chorniy` — A-2028, чорний
- `a-2028-z-nakladkoyu-pid-cilindr-chorniy` — A-2028 з накладкою під циліндр, чорний
- `a-2028-z-nakladkoyu-pid-wc-chorniy` — A-2028 з накладкою під WC, чорний
- `a-2028-sc-satin-hrom` — A-2028 SC, сатин хром
- `a-2028-sc-z-nakladkoyu-pid-cilindr-satin-hrom` — A-2028 SC з накладкою під циліндр, сатин хром
- `a-2028-sc-z-nakladkoyu-pid-wc-satin-hrom` — A-2028 SC з накладкою під WC, сатин хром
- `a-2029-chorniy` — A-2029, чорний
- `a-2029-z-nakladkoyu-pid-cilindr-chorniy` — A-2029 з накладкою під циліндр, чорний
- `a-2029-z-nakladkoyu-pid-wc-chorniy` — A-2029 з накладкою під WC, чорний
- `a-2029-mn-satin-nikel` — A-2029 MN, сатин нікель
- `a-2029-mn-z-nakladkoyu-pid-cilindr-satin-nikel` — A-2029 MN з накладкою під циліндр, сатин нікель
- `a-2029-mn-z-nakladkoyu-pid-wc-satin-nikel` — A-2029 MN з накладкою під WC, сатин нікель
- `a-2030-chorniy` — A-2030, чорний
- `a-2030-z-nakladkoyu-pid-cilindr-chorniy` — A-2030 з накладкою під циліндр, чорний
- `a-2030-z-nakladkoyu-pid-wc-chorniy` — A-2030 з накладкою під WC, чорний
- `a-2030-sb-matova-latun` — A-2030 SB, матова латунь
- `a-2030-sb-z-nakladkoyu-pid-cilindr-matova-latun` — A-2030 SB з накладкою під циліндр, матова латунь
- `a-2030-sb-z-nakladkoyu-pid-wc-matova-latun` — A-2030 SB з накладкою під WC, матова латунь
- `z-1812-chorniy` — Z-1812, чорний
- `z-1812-z-nakladkoyu-pid-cilindr-chorniy` — Z-1812 з накладкою під циліндр, чорний
- `z-1812-z-nakladkoyu-pid-wc-chorniy` — Z-1812 з накладкою під WC, чорний
- `z-1812-mc-matoviy-hrom` — Z-1812 MC, матовий хром
- `z-1812-mc-z-nakladkoyu-pid-cilindr-matoviy-hrom` — Z-1812 MC з накладкою під циліндр, матовий хром
- `z-1812-mc-z-nakladkoyu-pid-wc-matoviy-hrom` — Z-1812 MC з накладкою під WC, матовий хром
- `z-1813-chorniy` — Z-1813, чорний
- `z-1813-z-nakladkoyu-pid-cilindr-chorniy` — Z-1813 з накладкою під циліндр, чорний
- `z-1813-z-nakladkoyu-pid-wc-chorniy` — Z-1813 з накладкою під WC, чорний
- `z-1813-sc-satin-hrom` — Z-1813 SC, сатин хром
- `z-1813-sc-z-nakladkoyu-pid-cilindr-satin-hrom` — Z-1813 SC з накладкою під циліндр, сатин хром
- `z-1813-sc-z-nakladkoyu-pid-wc-satin-hrom` — Z-1813 SC з накладкою під WC, сатин хром
- `z-1813-mn-satin-nikel` — Z-1813 MN, сатин нікель
- `z-1813-mn-z-nakladkoyu-pid-cilindr-satin-nikel` — Z-1813 MN з накладкою під циліндр, сатин нікель
- `z-1813-mn-z-nakladkoyu-pid-wc-satin-nikel` — Z-1813 MN з накладкою під WC, сатин нікель
- `z-1814-chorniy` — Z-1814, чорний
- `z-1814-z-nakladkoyu-pid-cilindr-chorniy` — Z-1814 з накладкою під циліндр, чорний
- `z-1814-z-nakladkoyu-pid-wc-chorniy` — Z-1814 з накладкою під WC, чорний
- `z-1814-sc-satin-hrom` — Z-1814 SC, сатин хром
- `z-1814-sc-z-nakladkoyu-pid-cilindr-satin-hrom` — Z-1814 SC з накладкою під циліндр, сатин хром
- `z-1814-sc-z-nakladkoyu-pid-wc-satin-hrom` — Z-1814 SC з накладкою під WC, сатин хром
- `z-1814-mn-satin-nikel` — Z-1814 MN, сатин нікель
- `z-1814-mn-z-nakladkoyu-pid-cilindr-satin-nikel` — Z-1814 MN з накладкою під циліндр, сатин нікель
- `z-1814-mn-z-nakladkoyu-pid-wc-satin-nikel` — Z-1814 MN з накладкою під WC, сатин нікель

</details>


### 3.3. Комплектуючі — 94 товари (пріоритет 4)

Короби, лиштва, добори, капітелі, розширювачі, плінтуси, завіси, замки, розетки, цоколі, профілі, кілька ручок A-2022.

**Потрібно:** опис 150–250 слів (роль елемента в дверному блоці, коли потрібен, як підібрати розмір), характеристики (розміри з назви, матеріал і покриття — лише підтверджені, кольори), 3 FAQ (скільки добору потрібно, на одну чи дві сторони лиштва тощо).

**Що вже відомо з офіційних сайтів виробників:**
- **ArtPorte:** коробка «МДФ + євробрус 2060×80×38» і «2060×100×38», поліпропілен; лиштва «2150×80×10×Н30/Н45»; добірна планка з двома пазами «2070×100×10» і «2070×200×10»; плінтус 2000×16×80 і 2800×16×80. Значення Н30/Н45 на сайті не пояснене.
- **Omega (коробка Inside):** товщина 32 мм, ширина 86 мм (робоча 80 мм), фанера + МДФ, фарбування; лиштва 12×80×2150 мм; добір 12 мм, довжина 2070 мм; кольори RAL / KEMICHAL.
- **Status Doors:** добір G.10.1 2070×90 мм з одним пазом; добір також 2070×180 і 2070×400. На картках сайту вказано 2200 мм — розбіжність треба уточнити.
- **Estet, Gorgania:** на офіційних сайтах характеристик комплектуючих немає; уточнювати в постачальника.

<details>
<summary>Список slug (94)</summary>

- `komplekt-korobu-teleskopichnogo-2060-80-38mm-artporte` — Комплект коробу телескопічного 2060*80*38мм ArtPorte (Artporte)
- `komplekt-korobu-teleskopichnogo-2060-100-38mm-artporte` — Комплект коробу телескопічного 2060*100*38мм ArtPorte (Artporte)
- `komplekt-lishtvi-na-1-storonu-2150-80-10-h30-artporte` — Комплект лиштви на 1 сторону 2150*80*10*H30 ArtPorte (Artporte)
- `komplekt-lishtvi-na-1-storonu-2150-80-10h45-artporte` — Комплект лиштви на 1 сторону 2150*80*10H45 ArtPorte (Artporte)
- `dobir-100mm-artporte` — Добір 100мм ArtPorte (Artporte)
- `dobir-200mm-artporte` — Добір 200мм ArtPorte (Artporte)
- `plintus-2000-16-80-artporte` — Плінтус 2000*16*80 ArtPorte (Аксесуари)
- `plintus-2800-16-80-artporte` — Плінтус 2800*16*80 ArtPorte (Аксесуари)
- `komplekt-teleskopichnogo-korobu-estet` — Комплект телескопічного коробу Estet (Estet)
- `komplekt-komplanarnogo-korobu-estet` — Комплект компланарного коробу Estet (Estet)
- `komplekt-teleskopichnoyi-lishtvi-na-1-storonu-estet` — Комплект телескопічної лиштви на 1 сторону Estet (Estet)
- `komplekt-teleskopichnoyi-lishtvi-na-dvi-storoni-estet` — Комплект телескопічної лиштви на дві сторони Estet (Estet)
- `komplekt-komplanarnoyi-lishtvi-na-1-storonu-estet` — Комплект компланарної лиштви на 1 сторону Estet (Estet)
- `komplekt-doboru-100mm-estet` — Комплект добору 100мм Estet (Estet)
- `komplekt-doboru-150mm-estet` — Комплект добору 150мм Estet (Estet)
- `komplekt-doboru-200mm-estet` — Комплект добору 200мм Estet (Estet)
- `plintus-2000-80-10-estet` — Плінтус 2000*80*10 Estet (Estet)
- `zavisi-semom-prihovani-2-sht.-vrizka-fabrichna-artporte` — Завіси СЕМОМ приховані (2 шт.) + врізка фабрична ArtPorte (Artporte)
- `zamok-agb-polaris-magnitniy-vidpovidna-planka` — Замок AGB Polaris магнітний + відповідна планка (AGB)
- `zavisa-prihovana-otlav` — Завіса прихована Otlav (Otlav)
- `rozetka-84-84-2-sht.-omega` — Розетка (84*84) 2 шт. Omega (Omega)
- `cokol-84-180-2-sht.-omega` — Цоколь (84*180) 2 шт. Omega (Omega)
- `kapitel-omega` — Капітель Omega (Omega)
- `prihovani-zavisi-vrizka-vid-tm-omega` — Приховані завіси +врізка від ТМ Omega (Omega)
- `alyuminiieviy-torec-phoenix` — Алюмінієвий торець Phoenix (Аксесуари)
- `plintus-2070-80-16mm-phoenix` — Плінтус 2070*80*16мм Phoenix (Плінтус)
- `plintus-2070-80-16mm-vologostiykiy-mdf-phoenix` — Плінтус 2070*80*16мм, вологостійкий МДФ, Phoenix (Плінтус)
- `plintus-2070-80-10mm-phoenix` — Плінтус 2070*80*10мм Phoenix (Плінтус)
- `plintus-2070-80-10mm-vologostiykiy-mdf-phoenix` — Плінтус 2070*80*10мм, вологостійкий МДФ, Phoenix (Плінтус)
- `korob-80h40x2070-z-ushchilnyuvachem-derevo-mdf-teleskop-korfad` — Короб 80х40x2070 з ущільнювачем Дерево + МДФ ТЕЛЕСКОП Korfad (Korfad)
- `korob-100h40h2070-z-ushchilnyuvachem-derevo-mdf-teleskop-korfad` — Короб 100х40х2070 з ущільнювачем Дерево + МДФ ТЕЛЕСКОП Korfad (Korfad)
- `lishtva-ploska-70h10x2150-z-krilom-30-mdf-teleskop-korfad` — Лиштва плоска 70х10x2150 з крилом 30 МДФ ТЕЛЕСКОП Korfad (Korfad)
- `dobirna-doshka-100h10x2070-mdf-teleskop-korfad` — Добірна дошка 100х10x2070 МДФ ТЕЛЕСКОП Korfad (Korfad)
- `dobirna-doshka-200h10h2070-mdf-teleskop-korfad` — Добірна дошка 200х10х2070 МДФ ТЕЛЕСКОП Korfad (Korfad)
- `plintus-pidlogoviy-mdf-ps-standard-80h16h2060-korfad` — Плінтус підлоговий МДФ PS Standard 80х16х2060 Korfad (Korfad)
- `lishtva-ploska-z-krilom-70h10h2150-krilo-50-40-mdf-teleskop-korfad` — Лиштва плоска з крилом 70х10х2150 (крило 50/40) МДФ ТЕЛЕСКОП Korfad (Korfad)
- `lishtva-ploska-z-krilom-120h10h2200-krilo-30-20-mdf-teleskop-korfad` — Лиштва плоска з крилом 120х10х2200 (крило 30/20) МДФ ТЕЛЕСКОП Korfad (Korfad)
- `lishtva-z-kannelyurami-z-krilom-80h16h2150-krilo-30-mdf-teleskop-korfad` — Лиштва з каннелюрами з крилом 80х16х2150 (крило 30) МДФ ТЕЛЕСКОП Korfad (Korfad)
- `h-podibniy-z-iednuvalniy-profil-38h2070-mdf-korfad` — H-подібний з'єднувальний профіль 38х2070 МДФ Korfad (Korfad)
- `kapitel-895-995-1095-1195h58h100-mm-korfad` — Капітель 895/995/1095/1195х58х100 мм Korfad (Korfad)
- `kapitel-1295-1395h58h100-mm-korfad` — Капітель 1295/1395х58х100 мм Korfad (Korfad)
- `kapitel-1495-1595h58h100-mm-korfad` — Капітель 1495/1595х58х100 мм Korfad (Korfad)
- `kapitel-1695-1895h58h100-mm-korfad` — Капітель 1695/1895х58х100 мм Korfad (Korfad)
- `komplekt-teleskopichnogo-korobu-ral-9003-2150h80mm-status` — Комплект телескопічного коробу Ral 9003 2150х80мм Status (Status)
- `komplekt-teleskopichnoyi-lishtvi-ral-9003-2200h80mm-status` — Комплект телескопічної лиштви Ral 9003 2200х80мм Status (Status)
- `komplekt-doboru-ral-9003-2200h90mm-status` — Комплект добору Ral 9003 2200х90мм Status (Status)
- `komplekt-doboru-ral-9003-2200h180mm-status` — Комплект добору Ral 9003 2200х180мм Status (Status)
- `komplekt-doboru-ral-9003-2200h400mm-status` — Комплект добору Ral 9003 2200х400мм Status (Status)
- `komplekt-teleskopichnogo-korobu-37-82-2070mm-estet-doors` — Комплект телескопічного коробу 37*82*2070мм Estet Doors (Estet)
- `komplekt-komplanarnogo-korobu-37-82-2070mm-estet-doors` — Комплект компланарного коробу 37*82*2070мм Estet Doors (Estet)
- `komplekt-teleskopichnoyi-lishtvi-10-80-2150mm-estet-doors` — Комплект телескопічної лиштви 10*80*2150мм Estet Doors (Estet)
- `komplekt-komplanarnoyi-lishtvi-10-84-2150mm-estet-doors` — Комплект компланарної лиштви 10*84*2150мм Estet Doors (Estet)
- `komplekt-doboru-10-100-2070mm-estet-doors` — Комплект добору 10*100*2070мм Estet Doors (Estet)
- `komplekt-doboru-10-150-2070mm-estet-doors` — Комплект добору 10*150*2070мм Estet Doors (Estet)
- `komplekt-doboru-10-200-2070mm-estet-doors` — Комплект добору 10*200*2070мм Estet Doors (Estet)
- `dverna-korobka-optima-mdf-100-n-nezarizana-inside-2210h600-700-800-900-lishtva-1-storona-2-5-sht-komplekt-maxi` — Дверна коробка Optima МДФ (100) N(незарізана) INSIDE 2210х600,700,800,900 (лиштва 1 сторона) (2,5 шт комплект) Maxi (BonaDoors)
- `dverna-korobka-teleskop-derevo-mdf-80h40h2100-2-5-sht-komplekt-maxi` — Дверна коробка Телескоп Дерево / МДФ 80х40х2100 (2,5 шт комплект) Maxi (BonaDoors)
- `dverna-korobka-komplanarna-derevo-mdf-79h40h-2100-2-5-sht-komplekt-maxi` — Дверна коробка компланарна Дерево / МДФ 79х40х*2100 (2,5 шт комплект) Maxi (BonaDoors)
- `lishtva-teleskop-z-krilom-30-mdf-70h10h2150-2-5-sht-komplekt-maxi` — Лиштва Телескоп з крилом 30 МДФ 70х10х2150 (2,5 шт комплект) Maxi (BonaDoors)
- `lishtva-teleskop-z-krilom-50-mdf-70h10h2150-2-5-sht-komplekt-maxi` — Лиштва Телескоп з крилом 50 МДФ 70х10х2150 (2,5 шт комплект) Maxi (BonaDoors)
- `lishtva-komplanar-mdf-80h10h2150-2-5-sht-komplekt-maxi` — Лиштва компланар МДФ 80х10х2150 (2,5 шт комплект) Maxi (BonaDoors)
- `rozshiryuvach-teleskop-mdf-90h10h2070-2-5-sht-komplekt-maxi` — Розширювач Телескоп МДФ 90х10х2070 (2,5 шт комплект) Maxi (BonaDoors)
- `rozshiryuvach-teleskop-mdf-180h10h2070-2-5-sht-komplekt-maxi` — Розширювач Телескоп МДФ 180х10х2070 (2,5 шт комплект) Maxi (BonaDoors)
- `rozshiryuvach-teleskop-mdf-250h10h2070-2-5-sht-komplekt-maxi` — Розширювач Телескоп МДФ 250х10х2070 (2,5 шт комплект) Maxi (BonaDoors)
- `rozshiryuvach-teleskop-mdf-400h10h2070-2-5-sht-komplekt-maxi` — Розширювач Телескоп МДФ 400х10х2070 (2,5 шт комплект) Maxi (BonaDoors)
- `plintus-mdf-80h12h2000-maxi` — Плінтус МДФ 80х12х2000 Maxi (BonaDoors)
- `komplekt-teleskopichnogo-korobu-2070-90-38mm-korfad-exellence` — Комплект телескопічного коробу 2070*90*38мм Korfad Exellence (Korfad)
- `komplekt-komplanarnogo-korobu-2070-90-45mm-korfad-exellence` — Комплект компланарного коробу 2070*90*45мм Korfad Exellence (Korfad)
- `komplekt-insayd-korobu-2100-90-32mm-korfad-exellence` — Комплект Інсайд коробу 2100*90*32мм Korfad Exellence (Korfad)
- `komplekt-teleskopichnoyi-lishtvi-z-krilom-30-na-1-storonu-2150-70-10mm-korfad-exellence` — Комплект телескопічної лиштви з крилом 30 на 1 сторону 2150*70*10мм Korfad Exellence (Korfad)
- `komplekt-teleskopichnoyi-lishtvi-z-krilom-50-na-1-storonu-2150-70-10mm-korfad-exellence` — Комплект телескопічної лиштви з крилом 50 на 1 сторону 2150*70*10мм Korfad Exellence (Korfad)
- `komplekt-insayd-lishtvi-na-1-storonu-2150-80-12mm-korfad-exellence` — Комплект інсайд лиштви на 1 сторону 2150*80*12мм Korfad Exellence (Korfad)
- `komplekt-komplanarnoyi-lishtvi-na-1-storonu-2150-80-12mm-korfad-exellence` — Комплект компланарної лиштви на 1 сторону 2150*80*12мм Korfad Exellence (Korfad)
- `komplekt-doboru-2070-100-10mm-korfad-exellence` — Комплект добору 2070*100*10мм Korfad Exellence (Korfad)
- `komplekt-doboru-2070-200-10mm-korfad-exellence` — Комплект добору 2070*200*10мм Korfad Exellence (Korfad)
- `komplekt-doboru-2070-350-10mm-korfad-exellence` — Комплект добору 2070*350*10мм Korfad Exellence (Korfad)
- `prihovani-zavisi-z-fabrichnoyu-vrizkoyu` — Приховані завіси з фабричною врізкою (BonaDoors)
- `dverna-korobka-teleskop-80-gorgania` — Дверна коробка Телескоп 80 Gorgania (Gorgania)
- `dverna-korobka-teleskop-100-gorgania` — Дверна коробка Телескоп 100 Gorgania (Gorgania)
- `dverna-korobka-komplanar-80-gorgania` — Дверна коробка Компланар 80 Gorgania (Gorgania)
- `lishtva-teleskop-z-krilom-30-gorgania` — Лиштва Телескоп з крилом 30 Gorgania (Gorgania)
- `lishtva-teleskop-z-krilom-50-gorgania` — Лиштва Телескоп з крилом 50 Gorgania (Gorgania)
- `lishtva-komplanar-80-gorgania` — Лиштва Компланар 80 Gorgania (Gorgania)
- `dobir-100mm-gorgania` — Добір 100мм Gorgania (Gorgania)
- `dobir-150mm-gorgania` — Добір 150мм Gorgania (Gorgania)
- `dobir-200mm-gorgania` — Добір 200мм Gorgania (Gorgania)
- `zavisi-anselmi-140-z-fabrichnoyu-vrizkoyu-gorgania` — Завіси Anselmi 140 з фабричною врізкою Gorgania (Gorgania)
- `plintus-12-gorgania` — Плінтус 12 Gorgania (Gorgania)
- `plintus-16-gorgania` — Плінтус 16 Gorgania (Gorgania)
- `z-1319-sn-cp-z-nakladkoyu-pid-wc-matoviy-nikel-polirovaniy-hrom` — Z-1319 SN/CP з накладкою під WC, матовий нікель/полірований хром (Двері Україна)
- `a-2022-chorniy` — A-2022, чорний (без бренду)
- `a-2022-z-nakladkoyu-pid-cilindr-chorniy` — A-2022 з накладкою під циліндр, чорний (без бренду)
- `a-2022-z-nakladkoyu-pid-wc-chorniy` — A-2022 з накладкою під WC, чорний (без бренду)
- `a-2022-sc-satin-hrom` — A-2022 SC, сатин хром (без бренду)

</details>


### 3.4. Однакові title і description — 61 товар (пріоритет 5)

Google вважає ці сторінки копіями, бо в них однакові мета-теги.

**Потрібно:** унікальні `meta_title` (до 65 символів, з назвою моделі й розміром або кольором) і `meta_description` (130–165 символів). Потребує `replace_meta` — див. [розділ 6](#6-що-потребує-доробки-коду-перед-наповненням).

<details>
<summary>Групи з однаковим title</summary>

**«Придбати фарбовану лиштву до міжкімнатних дверей Estet в Україні»** — 3 товарів
- `komplekt-teleskopichnoyi-lishtvi-na-1-storonu-estet`
- `komplekt-teleskopichnoyi-lishtvi-na-dvi-storoni-estet`
- `komplekt-komplanarnoyi-lishtvi-na-1-storonu-estet`

**«Придбати фарбований добір до міжкімнатних дверей Estet в Україні. Розширювач до міжкімнатних дверей можна замовити на нашому сайті»** — 3 товарів
- `komplekt-doboru-100mm-estet`
- `komplekt-doboru-150mm-estet`
- `komplekt-doboru-200mm-estet`

**«Технічні двері - купити в Одесі в інтернет магазині | Bona-Doors»** — 19 товарів
- `tehno-3-2050-860-antracit-7024`
- `tehno-3-2050-960-antracit-7024`
- `tehno-3-2050-1200-antracit-7024`
- `tehno-1-2050-860-antracit-7024`
- `tehno-1-2050-960-antracit-7024`
- `tehno-1-2050-1200-antracit-7024`
- `tehno-1-2050-860-korichneva-shagren-ral-8017`
- `tehno-1-2050-960-korichneva-shagren-ral-8017`
- `tehno-1-2050-1200-korichneva-shagren-ral-8017`
- `tehno-baza-2070-860-antracit-ral-7024`
- `tehno-baza-2070-960-antracit-ral-7024`
- `tehno-baza-2070-860-korichneva-shagren-ral-8017`
- `tehno-baza-2070-960-korichneva-shagren-ral-8017`
- `tehnichni-ei-60-2070-870-shagren-ral-7035`
- `tehnichni-ei-60-2070-960-shagren-ral-7035`
- `tehnichni-ei-60-2070-1200-shagren-ral-7035`
- `tehnichni-ei-60-2070-1200-shagren-ral-7024`
- `tehnichni-ei-60-2070-960-shagren-ral-7024`
- `tehnichni-ei-60-2070-870-shagren-ral-7024`

**«Вхідні двері Fort-M Тріо-Греція - купити в Одесі в інтернет магазині | Bona-Doors»** — 3 товарів
- `trio-greciya-zi-sklom-fort-m-dveri-na-vulicyu`
- `trio-greciya-1200mm-zi-sklom-fort-m-dveri-na-vulicyu`
- `trio-greciya-1200mm-fort-m-dveri-na-vulicyu`

**«Вхідні двері Айрон - купити в Одесі в інтернет магазині | Bona-Doors»** — 4 товарів
- `ayron-v1-strong-dveri-na-vulicyu`
- `ayron-v2-strong-dveri-na-vulicyu`
- `ayron-v2-1200mm-strong-dveri-na-vulicyu`
- `ayron-v1-1200mm-strong-dveri-na-vulicyu`

**«Вхідні двері Вест - купити в Одесі в інтернет магазині | Bona-Doors»** — 3 товарів
- `vest-v1-strong-dveri-na-vulicyu`
- `vest-v2-strong-dveri-na-vulicyu`
- `vest-v3-strong-dveri-na-vulicyu`

**«Придбати фарбований плінтус в Україні. Плінтус від українського виробника в інтернет магазині | Bona-Doors»** — 23 товарів
- `plintus-farbovaniy-classic-mod.-pmc001-2800-70-10mm`
- `plintus-farbovaniy-classic-mod.-pmc002-2800-80-12mm`
- `plintus-farbovaniy-classic-mod.-pmc003-2800-90-10mm`
- `plintus-farbovaniy-classic-mod.-pmc004-2800-80-12mm`
- `plintus-farbovaniy-classic-mod.-pmc006-2800-70-12mm`
- `plintus-farbovaniy-classic-mod.-pmc007-2800-80-10mm`
- `plintus-farbovaniy-classic-mod.-pmc008-2800-80-16mm`
- `plintus-farbovaniy-classic-mod.-pmc009-2800-80-16mm`
- `plintus-farbovaniy-classic-mod.-pmc010-2800-100-10mm`
- `plintus-farbovaniy-provence-mod.-pmp011-2800-80-12mm`
- `plintus-farbovaniy-provence-mod.-pmp013-2800-60-16mm`
- `plintus-farbovaniy-provence-mod.-pmp014-2800-80-12mm`
- `plintus-farbovaniy-provence-mod.-pmp015-2800-100-16mm`
- `plintus-farbovaniy-provence-mod.-pmp016-2800-70-16mm`
- `plintus-farbovaniy-provence-mod.-pmp018-2800-100-12mm`
- `plintus-farbovaniy-provence-mod.-pmp020-2800-100-16mm`
- `plintus-farbovaniy-provence-mod.-pmp021-2800-100-12mm`
- `plintus-farbovaniy-provence-mod.-pmp022-2800-80-10mm`
- `plintus-farbovaniy-provence-mod.-pmp023-2800-80-12mm`
- `plintus-farbovaniy-provence-mod.-pmp024-2800-80-12mm`
- `plintus-farbovaniy-provence-mod.-pmp025-2800-80-12mm`
- `plintus-farbovaniy-provence-mod.-pmp026-2800-80-16mm`
- `plintus-farbovaniy-provence-mod.-pmp027-2800-100-16mm`

**«Вхідні двері Strimех Lama Bau - купити в Одесі в інтернет магазині | Bona-Doors»** — 3 товарів
- `bau-v1-lama-strimeh-dveri-na-vulicyu`
- `bau-v2-lama-strimeh-dveri-na-vulicyu`
- `bau-v1-1200mm-lama-strimeh-dveri-na-vulicyu`


</details>


### 3.5. Розсувні двері — 20 товарів (пріоритет 6)

Немає ціни, опису й характеристик. На сайті показується «Ціна за запитом».

**Потрібно:** ціни в адмінці (або свідоме рішення залишити «за запитом»), опис 200–300 слів, характеристики системи (тип механізму, максимальна вага й ширина полотна, комплектація), 3–4 FAQ.

<details>
<summary>Список slug (20)</summary>

- `ceiling-mount` — Ceiling Mount
- `classic` — Classic
- `ergon-living-t.e.` — Ergon Living t.e.
- `loft` — Loft
- `loft-black-metal` — Loft Black Metal
- `loft-fluts-glass` — Loft Fluts Glass
- `loft-gold-metal` — Loft Gold Metal
- `loft-two-fluts-glasses` — Loft Two Fluts Glasses
- `loft-two-glasses` — Loft Two Glasses
- `loft-white-metal` — Loft White Metal
- `magic` — Magic
- `magic-mirror` — Magic Mirror
- `mantion-saf-slim` — Mantion Saf-Slim
- `slido-classic-40-120p` — Slido Classic 40-120P
- `slido-classic-synchro-40-120p` — Slido Classic Synchro 40-120P
- `slido-classic-telescopic-80-120p` — Slido Classic Telescopic 80-120P
- `slido-design-100-s` — Slido Design 100-S
- `slido-design-70v` — Slido Design 70V
- `slido-design-80v` — Slido Design 80V
- `slido-t-snap` — Slido T-Snap

</details>


### 3.6. Інші товари з коротким описом (пріоритет 7)

Опис коротший за 40 слів. Окремо: у дверей Estet Grand 1D і 2D (`mizhkimnatni-dveri-grand-1d-1.31-estet-doors`, `mizhkimnatni-dveri-grand-2d-1.3-estet-doors`) український і російський описи однакові, тож RU-версії потрібен окремий текст.

<details>
<summary>Міжкімнатні двері (55)</summary>

- `mizhkimnatni-dveri-artporte-chikago` — 39 слів
- `mizhkimnatni-dveri-artporte-nyu-york` — 39 слів
- `mizhkimnatni-dveri-classic1-new` — 35 слів
- `mizhkimnatni-dveri-classic2-new` — 35 слів
- `mizhkimnatni-dveri-delicate` — 35 слів
- `mizhkimnatni-dveri-dream-glass-ral-9003-status` — 29 слів
- `mizhkimnatni-dveri-dream-ral-9003-status` — 28 слів
- `mizhkimnatni-dveri-dublin` — 35 слів
- `mizhkimnatni-dveri-elegance` — 32 слів
- `mizhkimnatni-dveri-elegance-wood` — 22 слів
- `mizhkimnatni-dveri-estet-mk-baza` — 29 слів
- `mizhkimnatni-dveri-estet-mk-diagonal` — 28 слів
- `mizhkimnatni-dveri-estet-mk-gorizontal` — 28 слів
- `mizhkimnatni-dveri-estet-mk-grand` — 28 слів
- `mizhkimnatni-dveri-estet-mk-provans` — 28 слів
- `mizhkimnatni-dveri-estet-mk-provans-glass` — 29 слів
- `mizhkimnatni-dveri-estet-mk-splint` — 28 слів
- `mizhkimnatni-dveri-florenciya-ral-9003-status` — 28 слів
- `mizhkimnatni-dveri-fly-ral-9003-status` — 28 слів
- `mizhkimnatni-dveri-geometry-ral-9003-status` — 28 слів
- `mizhkimnatni-dveri-glasso` — 32 слів
- `mizhkimnatni-dveri-gorgania-beskid` — 20 слів
- `mizhkimnatni-dveri-gorgania-dukach` — 20 слів
- `mizhkimnatni-dveri-gorgania-gerdan` — 20 слів
- `mizhkimnatni-dveri-gorgania-goverla` — 20 слів
- `mizhkimnatni-dveri-gorgania-grofa` — 20 слів
- `mizhkimnatni-dveri-gorgania-krasiya` — 20 слів
- `mizhkimnatni-dveri-gorgania-magura` — 20 слів
- `mizhkimnatni-dveri-gorgania-osiy` — 20 слів
- `mizhkimnatni-dveri-gorgania-osiy-plyus` — 20 слів
- `mizhkimnatni-dveri-gorgania-osiy-plyus-zi-sklom` — 23 слів
- `mizhkimnatni-dveri-gorgania-petros` — 20 слів
- `mizhkimnatni-dveri-gorgania-play` — 20 слів
- `mizhkimnatni-dveri-gorgania-play-plyus` — 20 слів
- `mizhkimnatni-dveri-gorgania-runa` — 20 слів
- `mizhkimnatni-dveri-gorgania-salba` — 20 слів
- `mizhkimnatni-dveri-gorgania-shelest` — 20 слів
- `mizhkimnatni-dveri-gorgania-stig` — 20 слів
- `mizhkimnatni-dveri-gorgania-strimba` — 20 слів
- `mizhkimnatni-dveri-gorgania-yavorina` — 20 слів
- `mizhkimnatni-dveri-gorgania-yavorina-plyus` — 20 слів
- `mizhkimnatni-dveri-lines` — 35 слів
- `mizhkimnatni-dveri-molding` — 35 слів
- `mizhkimnatni-dveri-molding-1b` — 32 слів
- `mizhkimnatni-dveri-molding-2b` — 35 слів
- `mizhkimnatni-dveri-omega-seriya-art-vision-a1-shpon` — 33 слів
- `mizhkimnatni-dveri-parallel` — 35 слів
- `mizhkimnatni-dveri-sky-moldig-black-ral-9003-status` — 29 слів
- `mizhkimnatni-dveri-sky-ral-9003-status` — 28 слів
- `mizhkimnatni-dveri-ultra` — 32 слів
- `mizhkimnatni-dveri-ultra-005-ral-9003-status` — 28 слів
- `mizhkimnatni-dveri-ultra-black-glass-120mm-ral-9003-status` — 30 слів
- `mizhkimnatni-dveri-ultra2-glass-satin-30mm-ral-9003-status` — 31 слів
- `mizhkimnatni-dveri-vertical-1v` — 35 слів
- `mizhkimnatni-dveri-vertical-2v` — 35 слів

</details>

<details>
<summary>Технічні та вхідні двері (11)</summary>

- `launzh-ak-ultra-qdoors-vhidni` — Вхідні двері, 0 слів
- `tehno-1-2050-1200-antracit-7024` — Технічні двері, 28 слів
- `tehno-1-2050-1200-korichneva-shagren-ral-8017` — Технічні двері, 28 слів
- `tehno-1-2050-860-antracit-7024` — Технічні двері, 28 слів
- `tehno-1-2050-860-korichneva-shagren-ral-8017` — Технічні двері, 28 слів
- `tehno-1-2050-960-antracit-7024` — Технічні двері, 28 слів
- `tehno-1-2050-960-korichneva-shagren-ral-8017` — Технічні двері, 28 слів
- `tehno-baza-2070-860-antracit-ral-7024` — Технічні двері, 33 слів
- `tehno-baza-2070-860-korichneva-shagren-ral-8017` — Технічні двері, 33 слів
- `tehno-baza-2070-960-antracit-ral-7024` — Технічні двері, 33 слів
- `tehno-baza-2070-960-korichneva-shagren-ral-8017` — Технічні двері, 34 слів

</details>

---

## 4. Сторінки, які треба створити або доповнити

### 4.1. Посадкові сторінки стилів міжкімнатних дверей

Зараз `/product-category/interior-doors/filter/styl=…` — це звичайні фільтри з canonical на загальний каталог, тож Google їх не індексує. Під запити на кшталт «міжкімнатні двері модерн Одеса» потрібні окремі сторінки.

| Сторінка | Фільтр | Title (приклад) |
|---|---|---|
| Міжкімнатні двері в стилі модерн | `styl=modern` | Міжкімнатні двері модерн — купити в Одесі \| Bona Doors |
| Класичні міжкімнатні двері | `styl=klassyka` | Класичні міжкімнатні двері — купити в Одесі \| Bona Doors |
| Двері в стилі неокласика | `styl=neoklassyka` | Двері неокласика — купити в Одесі \| Bona Doors |
| Двері в стилі мінімалізм | `styl=mynymalyzm` | Двері в стилі мінімалізм — купити в Одесі \| Bona Doors |

**Як створити:** адмінка → SEO → «Групи фільтрів» (`/admin/seo/filter-groups/create`). Тип товару — «Міжкімнатні двері», фільтр — стиль, slug (наприклад `dveri-modern`), назва, title, meta title і description українською та російською. Сторінка з'явиться за адресою `/product-category/interior-doors/{slug}`.

**Текст для кожної сторінки** (після доробки з [розділу 6](#6-що-потребує-доробки-коду-перед-наповненням)): 300–500 слів — ознаки стилю, до яких інтер'єрів пасує, кольори й фурнітура, 4–5 FAQ. UA + RU.

### 4.2. Кольорові сторінки

З 17.09.2026 ці сторінки відкриті для Google і мають автоматичні title, але не мають тексту. Сторінки з менш ніж 6 товарами отримують `noindex` автоматично.

| Сторінка | Адреса |
|---|---|
| Сірі міжкімнатні двері | `/product-category/interior-doors/filter/color=siry` |
| Міжкімнатні двері антрацит | `/product-category/interior-doors/filter/color=antracyt` |
| Міжкімнатні двері айворі | `/product-category/interior-doors/filter/color=avori` |
| Міжкімнатні двері бетон сірий | `/product-category/interior-doors/filter/color=beton-siry` |
| Білі міжкімнатні двері | `/product-category/interior-doors/filter/color=bily` |

**Потрібно:** вступний текст 150–300 слів на кожну (UA + RU): з чим поєднується колір, у яких інтер'єрах доречний, з якою фурнітурою. Поле для тексту з'явиться після доробки з [розділу 6](#6-що-потребує-доробки-коду-перед-наповненням).

> **Рішення перед наповненням: дві сторінки «Білі двері».** Є категорія `/product-category/bele-dvery` (29 відібраних моделей, уже має SEO-текст) і кольорова сторінка `/product-category/interior-doors/filter/color=bily` (81 модель). Вони конкурують за один запит. Треба вирішити, яка головна, і дати другій інший фокус або canonical на головну.

### 4.3. Категорії аксесуарів без опису

Немає description і тексту: **Замок**, **Механізм**, **Профіль**, **Алюмінієвий торець**, **Притворна планка**, **Розетка**, **Цоколь**. Порожні категорії **Плінтус Estet**, **Завіса СЕМОМ прихована**, **Ламінат**, **Вінілова підлога** вже закриті `noindex`: або наповнити їх товарами, або прибрати.

**Потрібно:** meta description (130–165 символів) і SEO-текст 200–400 слів для кожної непорожньої категорії. **Де:** адмінка → категорія товарів → поля «SEO title», «SEO text», meta.

### 4.4. Сторінки послуг

`/services` (~70 слів), `/services/konsultatsiia`, `/services/montazh-dverei`, `/services/vyklyk-maistra` (~100 слів кожна). RU-версія «Консультації» — у «Виявлено – не проіндексовано».

**Потрібно:** 400–700 слів на кожну послугу — що входить, етапи, терміни, вартість або від чого вона залежить, географія (Одеса), 4–6 FAQ. **Де:** адмінка → «Послуги».

### 4.5. «Наші роботи»

Сторінка є в головному меню, але порожня («Роботи поки не додані»). Потрібно 3–6 реальних проєктів з фото, описом і використаними моделями. Додається в адмінці.

---

## 5. Російська версія

Перевірено всі 1 160 сторінок `/ru/` (17.09.2026). Шаблони, меню, футер, кошик, оформлення й конфігуратор переведені повністю. Юридичні реквізити продавця (ФОП, адреса, призначення платежу) українською лишаються навмисно — це офіційні дані.

**Правило для API:** кожен запис наповнення обов'язково містить `ru`. Російський текст пишеться як окремий текст природною російською, а не машинний переклад українського; slug, артикули, бренди та RAL-коди не перекладаються.

### 5.1. Статті блогу без російської версії — 20

З 32 статей лише 12 мають російську версію. Потрібно написати RU-версії (заголовок, текст, meta title, meta description, FAQ) — у блозі для кожної мови можна задати власний slug.

- `/blog/mizhkimnatni-dveri-gid`
- `/blog/dveri-danapris-oglyad-brendu`
- `/blog/dveri-porte-italiyskiy-styl`
- `/blog/bili-dveri-ral-9003-chym-osoblyvyi-tsei-vidtinok-dlia-interi`
- `/blog/estet-doors-oglyad-brendu`
- `/blog/dveri-verona-porta-nova-ohliad-kolektsii-ta-dyzainu`
- `/blog/dveri-diportes-oglyad-kolekciy`
- `/blog/dveri-airone-porivnyannya`
- `/blog/kolekciya-bona-dea-pryklad-oformlennya`
- `/blog/dveri-amega-ohliad-brendu-kolektsii-ta-porivniannia-modelei`
- `/blog/comeo-porte-oglyad-modeley`
- `/blog/furnitura-v-antychnomu-styli`
- `/blog/dveri-polaris-v-odesi-ohliad-kolektsii-ta-vybir-modeli`
- `/blog/dveri-artporte-krashchi-modeli`
- `/blog/mahazyn-dverey-poblyzu`
- `/blog/dverni-ruchky-chop-hrom`
- `/blog/kolekciya-bona-quattro-inter-yer`
- `/blog/vhidni-dveri-gid`
- `/blog/vhidni-dveri-neo-fort-oglyad`
- `/blog/perfekt-door-yak-obraty-vhidni-dveri`

### 5.2. Назви товарів українською на RU-сторінках — 9

| Slug | Назва на /ru/ зараз | 
|---|---|
| `luce-p-biliy` | LUCE P, білий |
| `mirou-ultra-qdoors-vhidni` | Мироу Ультра Qdoors вхідні |
| `plintus-farbovaniy-provence-mod.-pmp018-2800-100-12mm` | Плінтус фарбований Provence мод. PMP018 2800*100*12мм |
| `tehnichni-ei-60-2070-1200-shagren-ral-7024` | Технические ЕІ 60 2070*1200 шагрень RAL 7024 |
| `tehnichni-ei-60-2070-1200-shagren-ral-7035` | Технические ЕІ 60 2070*1200 шагрень RAL 7035 |
| `tehnichni-ei-60-2070-870-shagren-ral-7024` | Технические ЕІ 60 2070*870 шагрень RAL 7024 |
| `tehnichni-ei-60-2070-870-shagren-ral-7035` | Технические ЕІ 60 2070*870 шагрень RAL 7035 |
| `tehnichni-ei-60-2070-960-shagren-ral-7024` | Технические ЕІ 60 2070*960 шагрень RAL 7024 |
| `tehnichni-ei-60-2070-960-shagren-ral-7035` | Технические ЕІ 60 2070*960 шагрень RAL 7035 |

Для технічних дверей `ЕІ 60` написано кирилицею («Е» та українська «І») — правильно латиницею `EI 60` в обох мовах. Мироу Ультра: «вхідні» → «входные»; LUCE P: «білий» → «белый»; плінтус Provence: «Плінтус фарбований» → «Плинтус крашеный».

### 5.3. Описи українською на RU-сторінках — 3

- `komplekt-doboru-ral-9003-2200h90mm-status`
- `mizhkimnatni-dveri-geometry-ral-9003-status`
- `plintus-farbovaniy-provence-mod.-pmp018-2800-100-12mm`

Їх можна включити в наповнення через API з `"replace_content": true` (RU-опис короткий і українською).

### 5.4. Meta title / description українською на RU-сторінках — 4

| Сторінка | Title зараз |
|---|---|
| `komplekt-komplanarnoyi-lishtvi-inside-na-2-storoni-omega` | Комплект компланарної Inside лиштви від виробника Omega - купити в Одесі в інтернет магази |
| `mizhkimnatni-dveri-classic2-new` | Міжкімнатні двері Classic2 NEW - купити в Одесі в інтернет магазині | Bona-Doors |
| `mizhkimnatni-dveri-molding` | Міжкімнатні двері Molding - купити в Одесі в інтернет магазині | Bona-Doors |
| `product-category/aksessuar/manufacturer/dveri-ukraina` | Аксессуары Двері Україна — купить в Bona Doors |

Для товарів — поле `meta_title`/`meta_description` RU (потребує `replace_meta`, бо поля не порожні). Для виробника «Двері Україна» — виправити RU-назву бренду (див. 5.5).

### 5.5. Довідники в адмінці (не через API, 15 хвилин вручну)

| Що | Де в адмінці | Зараз RU | Має бути RU |
|---|---|---|---|
| Колір «Білий пп» | Кольори | Білий пп | Белый пп (27 товарів і конфігуратор) |
| Колір «Сатин білий» | Кольори | Сатин білий | Сатин белый |
| Бренд «Двері Україна» | Бренди | Двері Україна | Двери Украина |
| Опції відкривання «ліве / праве / ліве внутрішнє (INSIDE) / праве внутрішнє (INSIDE)» | Поля типу товару → атрибути | українською | левое / правое / левое внутреннее (INSIDE) / правое внутреннее (INSIDE) |
| Характеристика «Колір:» у стінових панелей | Товар → Характеристики | Колір: | Цвет: |

Сторінки з неперекладеними опціями відкривання:
- `mizhkimnatni-dveri-comeo-porte-plato-ral-9003-z-komplanarnim-pogonazhem`
- `mizhkimnatni-dveri-omega-seriya-lines-s2`
- `mizhkimnatni-dveri-vertical-1v`

---

## 6. Що потребує доробки коду перед наповненням

| Завдання | Навіщо | Обсяг |
|---|---|---|
| **Власний SEO-текст для груп фільтрів** | Зараз сторінки груп фільтрів показують загальний текст «Міжкімнатні двері» — посадкові сторінки стилів будуть дублікатами | Поле тексту в адмінці групи фільтрів + виведення на сторінці |
| **Текст для кольорових сторінок** | Кольорові сторінки не мають поля для тексту | Простіше за все — зробити кожен колір групою фільтрів після пункту вище |
| **Імпорт для груп фільтрів** (необов'язково) | Щоб наповнювати посадкові сторінки через той самий процес, а не вручну | Розширити імпортер категорій або додати окремий |

---

## 7. Правила написання

**Факти**
- Не вигадувати розміри, матеріали, гарантії, країну виробництва чи дизайнерів. Джерела: дані товару на сайті, офіційні сайти виробників, підтверджені дилерські сторінки.
- Якщо факт не підтверджено, його не згадують. Краще «уточнюйте в менеджера», ніж неправда.
- Не згадувати ціни в тексті: вони змінюються.
- Не згадувати товари, яких немає в асортименті (наприклад, віконні ручки), а також готелі, ресторани й біографії дизайнерів — вони не допомагають покупцю.

**Мова і стиль**
- Обидві мови обов'язкові: природна сучасна українська (не калька з російської) та природна російська.
- Лапки «», апостроф ’.
- Без «найкращий», «преміальний», «ідеальний», без знаків оклику та емодзі.
- Варіанти одного товару (колір, розмір, накладка) не повинні бути копіями одне одного: абзаци про варіант мають відрізнятися.

**Структура і обсяг**

| Тип | Опис | FAQ | meta_title | meta_description |
|---|---|---|---|---|
| Двері | 200–300 слів, один `<h2>`, 2–4 `<h3>` | 3–4 | до 65 символів | 130–165 символів |
| Ручки, комплектуючі | 150–250 слів | 3 | до 65 символів | 130–165 символів |
| Посадкові сторінки стилів | 300–500 слів | 4–5 | до 65 символів | 130–165 символів |
| Кольорові сторінки, категорії | 150–400 слів | — | — | 130–165 символів |
| Послуги | 400–700 слів | 4–6 | до 65 символів | 130–165 символів |

**Шаблон meta:** «{Назва} — купити в Одесі | Bona Doors» / «{Название} — купить в Одессе | Bona Doors». Опис має містити Одесу / Одессу та доставку по Україні / Украине.

**Блок «Де купити»** (можна в кінці опису):
салони Bona Doors в Одесі — ТЦ «Гіпермаркет Дверей» (вул. Краснова, 12А) і ТЦ «МегаДім» (вул. Георгія Липського, 135); доставка по Україні; замір і монтаж в Одесі; оплата частинами через monobank або ПриватБанк.

---

## 8. Що вже зроблено

- **Колекція Korfad ALIANO AL-01—AL-07:** унікальні описи, характеристики, FAQ та meta українською і російською. Файли `2026_09_22_korfad_aliano_batch_01.json` та `2026_09_22_korfad_aliano_batch_02.json`; факти звірені з офіційним каталогом KORFAD і доступними опціями на Bona Doors.
- **Колекція Korfad ALUMINIUM LOFT PLATO ALP-01, ALP-02, ALP-03, ALP-07:** окремі описи моделей, характеристики, FAQ та meta українською і російською. Файл `2026_09_22_korfad_aluminium_loft_plato_batch_01.json`; геометрію молдингів, конструкцію та варіанти звірено з офіційним каталогом KORFAD і картками Bona Doors.
- **Колекція Korfad CLASSICO CL-02, CL-05, CL-07, CL-08, CL-09:** унікальні описи з урахуванням геометрії фільонок і скління, 12 характеристик, 4 FAQ та meta українською і російською. Файл `2026_09_22_korfad_classico_batch_01.json`; декори, розміри й варіанти скла звірено з картками Bona Doors та офіційним каталогом KORFAD.
- **Колекція Korfad FLORENCE FL-01—FL-05:** окремі описи п’яти моделей з урахуванням геометрії скляних вставок, 12 характеристик, 4 FAQ та meta українською і російською. Файл `2026_09_22_korfad_florence_batch_01.json`; декори, розміри й варіанти скла звірено з картками Bona Doors та офіційним каталогом KORFAD.
- **Колекції Korfad DECO LOFT PLATO DLP-01, GLASS LOFT PLATO GLP-01/02, LOFT PLATO LP-01 та WOOD PLATO WP-01:** п’ять окремих описів з урахуванням молдингів, орієнтації накладного скла й характеру глухих полотен, 12 характеристик, 4 FAQ та meta українською і російською. Файл `2026_09_23_korfad_plato_batch_01.json`; доступні декори й опції звірено з картками Bona Doors, конструкцію, строки та гарантію — з офіційним каталогом KORFAD.
- **Колекція Korfad EXELLENCE ANIMAS, ASATI, ATLANT, BONETTI та BRILLAR:** окремі описи п’яти моделей з урахуванням алюмінієвих вставок або рельєфного рисунка, 12 характеристик, 4 FAQ та meta українською і російською. Файл `2026_09_23_korfad_exellence_batch_01.json`; кольори й доступні опції звірено з картками Bona Doors, конструкцію, тип скла, строки й гарантію — з офіційним каталогом KORFAD.
- **Колекція Korfad EXELLENCE CALYPSO, CELESTIA, INFINITY, MARION та MONARCH:** окремі описи п’яти моделей з урахуванням горизонтального або вертикального рельєфу, гладкої поверхні чи рамкової композиції, 12 характеристик, 4 FAQ та meta українською і російською. Файл `2026_09_23_korfad_exellence_batch_02.json`; кольори й доступні опції звірено з картками Bona Doors, конструкцію, тип скла, строки й гарантію — з офіційним каталогом KORFAD.
- **Колекція Korfad EXELLENCE MONARCH GLASS, PAULINA, QUANTUM, RAMIRA та ROISEL:** окремі описи п’яти моделей з урахуванням сатинованого або чорного скла, ламаних рельєфних ліній, алюмінієвої вставки та рамкової геометрії, 12 характеристик, 4 FAQ та meta українською і російською. Файл `2026_09_23_korfad_exellence_batch_03.json`; кольори, розміри й відкривання звірено з картками Bona Doors, конструкцію, матеріали, строки й гарантію — з офіційним каталогом KORFAD.
- **Колекції Korfad EXELLENCE SALIN, SPARTA, WESTON, WETTER та MILANO ML-05:** окремі описи п’яти моделей з урахуванням рельєфних ліній, рамкових площин, канелюр або великих вставок зі скла сатин, 12 характеристик, 4 FAQ та meta українською і російською. Файл `2026_09_23_korfad_exellence_batch_04.json`; кольори, декори, розміри й відкривання звірено з картками Bona Doors, конструкцію, скло, строки й гарантію — з офіційним каталогом KORFAD.
- **Колекції Korfad PARMA PM-10, PIANO DELUXE PND-01, PORTO DELUXE PD-01/PD-03 та PORTO PR-01:** окремі описи п’яти моделей з урахуванням кількості, ширини й асиметрії скляних вставок або глухої рамкової площини, 12 характеристик, 4 FAQ та meta українською і російською. Файл `2026_09_23_korfad_porto_batch_01.json`; декори, скло, розміри й відкривання звірено з картками Bona Doors, конструкцію, строки й гарантію — з офіційним каталогом KORFAD.
- **Колекції Korfad PORTO PR-05, PR-08, PR-10, PR-12 та SCALEA SC-04:** окремі описи п’яти моделей з урахуванням глухих секцій, вертикального, горизонтального або комбінованого скління, 12 характеристик, 4 FAQ та meta українською і російською. Файл `2026_09_23_korfad_porto_batch_02.json`; декори, скло, розміри й відкривання звірено з картками Bona Doors, конструкцію, строки й гарантію — з офіційним каталогом KORFAD.
- **Колекції Korfad SANREMO SR-01, SANVITO SV-01 із дзеркалом і білим сатином та VALENTINO DELUXE VLD-01/VLD-03:** окремі описи п’яти карток з урахуванням суцільного дзеркала, великої сатинованої вставки й різної геометрії комбінованого скління, 12 характеристик, 4 FAQ та meta українською і російською. Файл `2026_09_23_korfad_sanvito_valentino_batch_01.json`; декори, заповнення, розміри й відкривання звірено з картками Bona Doors, конструкцію, строки й гарантію — з каталогом KORFAD.
- **Колекція Korfad VENECIA DELUXE VND-02/VND-04/VND-05:** окремі описи трьох моделей з урахуванням асиметричного комбінованого скління, трьох паралельних вставок і вертикальних ліній зі зміщеними сегментами, 12 характеристик, 4 FAQ та meta українською і російською. Файл `2026_09_23_korfad_venecia_batch_01.json`; декори, скло, розміри й відкривання звірено з картками Bona Doors, конструкцію, строки й гарантію — з каталогом KORFAD.
- **Ручки MVM ESSE Z-1210, STYLE Z-1220, FRIO SLIM Z-1220/E20, TANGO Z-1259, LOFT Z-1290, PRIMA Z-1311, OLA Z-1312, NEO Z-1319, QOOB SLIM Z-1320/E20, TEZA Z-1325, RAY Z-1355, DELIKATE Z-1420, SIMPLE Z-1807, APOLLO Z-1808, JUST Z-1809, SERENITY Z-1809/E20, WONDE Z-1810, BRAILLE Z-1811, MOTION Z-1811/E20, Z-1812, Z-1813 та Z-1814:** описи базових ручок і комплектів із накладкою під циліндр або WC у доступних покриттях, по 6 характеристик, 3 FAQ та meta українською і російською. Файли `2026_09_23_mvm_z1210_batch_01.json`, `2026_09_23_mvm_z1220_batch_01.json`, `2026_09_23_mvm_z1259_z1319_batch_01.json`, `2026_09_23_mvm_z1320_z1355_batch_01.json`, `2026_09_23_mvm_z1420_z1809_batch_01.json`, `2026_09_23_mvm_z1809e20_z1810_batch_01.json`, `2026_09_23_mvm_z1811_z1812_batch_01.json` і `2026_09_23_mvm_z1813_z1814_batch_01.json`; матеріал, серію, форму розетки й покриття звірено з офіційними картками MVM, комплектацію — з картками Bona Doors.
- **Ручки MVM ALMA A-1209, STYLE A-1220, RAY A-1355, SENSO A-2003 та TREND A-2004:** описи 24 базових ручок і комплектів із накладкою під циліндр або WC, по 6 характеристик, 3 FAQ та meta українською і російською. Файл `2026_09_25_mvm_a1209_a2004_batch_01.json`; алюмінієвий сплав, серію й форму розетки звірено з офіційними картками MVM, склад варіанта — з картками Bona Doors.
- **Ручки MVM MILLO A-2005, GROTTI A-2006, GIRONA A-2008 та BERLI SLIM A-2010/E20:** описи 27 базових ручок і комплектів із накладкою під циліндр або WC, по 6 характеристик, 3 FAQ та meta українською і російською. Файл `2026_09_25_mvm_a2005_a2010_batch_01.json`; матеріали, серію, форму й товщину розетки BERLI SLIM звірено з офіційними картками MVM, склад варіанта — з картками Bona Doors.
- **Ручки MVM DIPLOMAT SLIM A-2015/E20, PLANUM A-2017 та WAVE A-2018:** описи 27 базових ручок і комплектів із накладкою під циліндр або WC, по 6 характеристик, 3 FAQ та meta українською і російською. Файл `2026_09_25_mvm_a2015_a2018_batch_01.json`; матеріал, серію, форму розетки, дизайнера WAVE та окреме замовлення вставок DIPLOMAT SLIM звірено з офіційними картками MVM, склад варіанта — з картками Bona Doors.
- **Ручки MVM BRU A-2020, STEEL A-2021 та OLA A-2022:** описи 24 базових ручок і комплектів із накладкою під циліндр або WC, по 6 характеристик, 3 FAQ та meta українською і російською. Файл `2026_09_25_mvm_a2020_a2022_batch_01.json`; матеріал, форму розетки, дизайнерів BRU та OLA і відзнаку OLA у конкурсі DOTYK NATKHNENNYA 2021 звірено з офіційними картками MVM, склад варіанта — з картками Bona Doors.
- **7 категорій аксесуарів:** тексти та meta для «Замок», «Механізм», «Профіль», «Алюмінієвий торець», «Притворна планка», «Розетка» і «Цоколь» українською та російською. Файл `2026_09_22_accessory_categories.json`.
- **Імпорт категорій:** додано безпечний імпортер і команду `categories:apply-content`; дані потрапляють у звичайні поля адмінки, а ручний контент не перезаписується. Товарний імпортер підтримує явний `replace_meta` для перевірених груп із застарілими дублікатами.
- **10 прихованих дверей** (Danapris і BonaDoors): опис, характеристики, FAQ, meta. Файл `2026_09_17_hidden_doors.json`.
- **261 ручка DnD:** опис, 8 характеристик, 3 FAQ, meta. Дизайнер, розетка й матеріал звірені з dndhandles.it. Файл `2026_09_17_dnd_handles.json`.
- **Помилки в спільному описі Korfad** («МKorfad», «г.», «плівкой», «придає») виправлено, але дублікати залишаються.
- **Російська версія (код):** додано відсутні переклади оформлення замовлення (відділення Нової Пошти) та адмінки; sitemap тепер пов'язує українські й російські статті блогу з різними slug через `hreflang`.
- **Технічна частина SEO:** 301 для 320 адрес з 404, `noindex` для порожніх списків, виправлені canonical фільтрів, відкриті кольорові сторінки, сторінка «Виробники» замість сторінок шпалер, robots.txt без службових лічильників.

---

## 9. Конфігуратор — наступний етап

Зараз у конфігураторі 10 демонстраційних моделей і 3 ручки, тому аналітика по ньому поки не показова. Наповнення конфігуратора — окремий етап. Для нього знадобиться:

- перелік реальних моделей і ручок із каталогу, які варто показувати;
- для кожної моделі — кольори/покриття з назвами **UA і RU** (з довідника кольорів, див. 5.5: «Білий пп» у RU);
- зображення полотна без ручки для кожного покриття (формат як у `public/assets/door-configurator/v1/*-no-handle-v2.webp`);
- короткі підписи моделі UA і RU для карток конфігуратора;
- ціни беруться з каталогу автоматично.

Дані моделей зберігаються у `resources/data/door-configurator.json` (зв'язок зі slug товару й кольором каталогу).
