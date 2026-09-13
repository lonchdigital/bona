# Door configurator: production implementation

## Scope and visual brief

The approved local Door Studio prototype is now a storefront component on the existing `/door-configurator` and `/ru/door-configurator` routes. Keep the real storefront shell, header, footer, breadcrumbs, typography and cart. Do not publish the standalone prototype header, demo prices or demo checkout.

The room is the visual focus, with a compact product panel. Desktop shows six cards before scrolling. Mobile keeps the full scene aspect ratio and normal page scrolling: room, wall palette, scene, Door/Handle tabs, product cards, finishes, summary, download and approximation note. Room labels are 14px. The initial wall is white; the dark-grey swatch is #494949. Finish thumbnails remain stable when selected. All component CSS is scoped to avoid inherited legacy section spacing.

## Catalog and purchase boundary

- `resources/data/door-configurator.json` maps 12 prepared door models and 3 handles to existing product slugs and exact color IDs. It contains presentation data, not prices.
- `DoorConfiguratorService` loads current localized names, availability, base prices and color surcharges from the database. Only active products with positive base prices and stock/order statuses are offered. Missing or obsolete color mappings are excluded, never silently replaced.
- The displayed selection total includes the selected door finish and optional handle. Box, architrave, fitting, delivery and a complete technical assembly are not implicitly included. Handle visualization is schematic; the UI explicitly asks users to verify compatibility and dimensions before purchasing.
- POST `/door-configurator/cart` is protected by the site's CSRF middleware, throttling, server-side allowlists and price revalidation. Client price/quantity/attribute overrides are not trusted. Both cart lines are written in one transaction. A UUID stored per cart prevents duplicate additions on a retry; conflicting reuse returns 409.
- The native cart receives independent real catalog positions. Quantity changes, checkout and payment stay in the existing storefront flow. This release does not change payment provider logic or credentials.
- Adding the same existing cart position again refreshes its stored catalog price and color surcharge.

## Prepared imagery

`public/assets/door-configurator/v1` contains only the assets used by the approved prototype, optimized locally to WebP where appropriate. Room imagery was approved by the user. Product imagery is from the Bona Doors catalog; interior previews use prepared handle-free images so a selected handle is not drawn over a baked-in handle. Exterior models retain their actual hardware. These are visual approximations, not CAD, AR or a geometrically accurate 3D model.

Only prepared models/finishes appear in the tool. To add another model: obtain its catalog photography, prepare the room cutout and handle-free layer, add a versioned asset and exact slug/color mapping, then verify its geometry, live price, native-cart attributes and both locales. Do not map a render to an arbitrary available color. If a product slug changes, update this allowlist as part of that change.

The four room backgrounds are loaded on demand. Cutouts are loaded only when selected, and cached in memory for that page. PNG export is generated locally in the browser; no customer room photos are uploaded. Only the current visual selection is saved in local browser storage.

## SEO research and decision — 2026-09-14

This is qualitative search-result research, not a Keyword Planner export. No verified search-volume, CPC or keyword-difficulty data was available; no numerical demand estimates are claimed.

| Intent | Ukrainian focus | Russian focus |
| --- | --- | --- |
| Use the tool | конфігуратор дверей онлайн | конфигуратор дверей онлайн |
| Choose a product visually | підбір дверей онлайн, двері в інтер’єрі | подбор дверей онлайн, двери в интерьере |
| Compare finishes | як підібрати колір дверей, двері під колір стін | как подобрать цвет дверей, двери под цвет стен |
| Specific door categories | міжкімнатні, приховані, дзеркальні, вхідні двері | межкомнатные, скрытые, зеркальные, входные двери |

Search results contain existing Ukrainian door customization/visualization products. Avoid claiming to be Ukraine's only configurator. Product configurators and bespoke manufacturing also have different capabilities from this visual room tool:

- [Macko Door](https://mackodoor.com/)
- [MACKO individual design](https://macko.com.ua/doors/individualniy-dizayn/)
- [Doors City](https://doors.city/)
- [AVADA configurator demonstration](https://avada-media.com/uk/demo-apps/2d-door-configurator-monolith-architectural/)

Use one canonical tool page per locale now; do not create multiple near-duplicate keyword pages. Keep the existing explanatory blog article separate in intent: [Bona article](https://bona-doors.com.ua/blog/konfihurator-mizhkimnatnyh-dverey). Later decisions should use Search Console query/impression data plus a Ukrainian-region Keyword Planner export.

Implemented localized title, meta description, OG/Twitter metadata, self-canonical, existing reciprocal hreflang, both locales in the sitemap, crawlable explanatory text, FAQ and contextual internal links. Existing URLs are unchanged. Page-specific JSON-LD is WebPage + BreadcrumbList, referencing the existing shared organization. No fabricated ratings, product offers, FAQ rich-result promises or keyword-stuffed hidden text. [Google localized-version guidance](https://developers.google.com/search/docs/specialty/international/localized-versions).

## Verification and deployment

Automated coverage is in `tests/Feature/DoorConfiguratorTest.php` and `tests/js/door-configurator.test.mjs`; both test workflows run the frontend suite. Coverage includes localized catalog data, quote changes, unavailable/missing finishes, forged values, atomic cart lines, retries, current cart prices, JSON escaping and asset/translation integrity.

Browser checks use an isolated local preview database seeded with public catalog snapshots, not production customer data. Verified UA/RU rendering, 320/390/768/1440px widths, room/finish/type changes, default wall, exterior handle restrictions, selection persistence, zoom dialog, downloaded PNG, native cart and quantity recalculation. No real payment/order is submitted for testing.

The only new database table is `configurator_cart_requests`, linked to carts with cascading deletion. Deploy through the existing immutable Release workflow; do not copy `.env` or include unrelated local payment/scratch changes. No new production credentials are needed. Run production smoke checks after activation: both locale pages, loaded scene/assets, current catalog count and prices, native cart, `/up`. Rollback uses the existing previous-release switch; the additive table can remain for compatibility.
