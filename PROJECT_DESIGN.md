# Bona Doors storefront design direction

## Product and audience

Bona Doors is a premium, approachable door showroom for homeowners and interior professionals in Odesa. The storefront should make a technically complex purchase feel calm, tangible, and well guided.

## Visual direction

- Warm editorial minimalism rather than a generic ecommerce dashboard.
- Large Forum display type for page and section titles; Manrope for navigation, details, facts, and actions.
- Off-white paper surfaces, dark brown feature panels, muted warm-gray copy, and restrained gold accents.
- Product and project photography carries the visual weight. Avoid decorative gradients, unnecessary badges, and nested cards.
- Use rounded corners selectively: media, primary panels, and pill actions. Text sections should often be separated by spacing or fine rules instead of containers.

## Layout and rhythm

- Shared content width: `bona-shell`, maximum 1440px with 56px desktop, 32px tablet, and 20px mobile gutters.
- Internal pages begin with compact breadcrumbs and a split editorial hero.
- Desktop sections typically use 88–110px vertical rhythm; mobile sections use 64–76px.
- Alternate image and copy only when it helps scanning. Keep body copy readable at roughly 620–720px.
- Mobile layouts must become a natural single column; never preserve desktop card widths or fixed offsets.

## Interaction and accessibility

- Real links remain links; external links use `rel="noopener noreferrer"`.
- Focus states use the gold accent with a visible offset.
- Interactive targets are at least 44px high on touch screens.
- Images use meaningful alt text, lazy loading below the fold, and stable dimensions/aspect ratios.
- Empty or partially configured admin content must result in a deliberate empty state, never broken markup.

## Content policy

- Render the actual content managed in the admin panel. Do not invent awards, metrics, promises, team members, or project facts.
- Optional sections disappear when their corresponding content is empty.
- Ukrainian and Russian storefronts receive equivalent structure and translated interface labels.

## Configurator discovery (September 2026)

- Replace the homepage style-selection presentation at its existing position with a dark editorial configurator feature: explanatory copy, one clear action, and a room/product composite using the live configurator's assets and proportions.
- Preserve legacy style copy and photos in the homepage editor; a presentation selector can restore them without a content migration.
- Use a pale warm-gold catalog-menu entry, a compact gold topbar link between works and contacts, and a footer navigation link. Match UA/RU destinations and avoid duplicate configured footer links.
- On phones, place the example image after the introduction and before the features/action. Keep all copy legible and the image fully proportioned; no pretend controls or heavy configurator JavaScript on the homepage.

## Mobile catalog navigation (September 2026)

- Keep the desktop mega-menu unchanged. At `960px` and below, replace the flat catalog list with a two-level, full-height navigation flow inside the existing dark header overlay.
- The first level prioritizes shopping paths: one parent entry for the door catalog, then real non-door catalogue destinations such as wall panels, skirting boards, and door handles, followed by the configurator and all-products links. Never hide a configured storefront product type merely because it is promoted as a desktop header link.
- The door-catalog parent slides the navigation track horizontally to a second level containing the real configured door product types (including interior, hidden, and entrance doors when available). It is a button, not a fake link. The second level starts with an explicit back control and also offers a link to all products.
- Preserve Bona's warm editorial restraint: dark brown surface, cream type, thin low-contrast rules, Forum display type for primary catalogue choices, Manrope for utility labels, and restrained warm-gold emphasis. Use no card grid, generic pictograms, gradients, or borrowed brand styling.
- Motion communicates hierarchy only: a short horizontal slide with no bounce. Respect `prefers-reduced-motion` by removing the transition. Closing the drawer, pressing Escape, or crossing the desktop breakpoint resets the navigation to level one.
- Keep search and secondary company links available without competing with the catalogue hierarchy. Mobile targets must be at least 44px high; forward/back controls expose `aria-expanded`, `aria-controls`, and useful labels, and focus moves to the destination level then returns to the opener.
- Use the existing configured product types and categories as the source of truth, with Ukrainian and Russian parity. Long labels must wrap without colliding with arrows or the close control.

## Admin operational surfaces

- Keep the existing Bootstrap/Overpass admin shell, but make editing screens calm, compact, and task-led rather than decorative.
- Use a single visible locale at a time for bilingual content. The language switch changes the editing context while both translations remain part of the saved form.
- Represent hierarchy visually: parent groups use restrained panels, child items use compact nested rows and a subtle connecting rail.
- Ordering is direct manipulation through drag-and-drop, with keyboard arrow controls as a fallback. Do not expose raw order numbers when the position can be shown spatially.
- Every builder must cover enabled, disabled, empty, dragging, unsaved, saving, saved, validation-error, and responsive states.
- Use neutral white and warm-gray surfaces, 6–10px radii, fine borders, and existing admin controls. Avoid gradients, oversized cards, and ornamental dashboard styling.
