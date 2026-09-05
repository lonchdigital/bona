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

## Admin operational surfaces

- Keep the existing Bootstrap/Overpass admin shell, but make editing screens calm, compact, and task-led rather than decorative.
- Use a single visible locale at a time for bilingual content. The language switch changes the editing context while both translations remain part of the saved form.
- Represent hierarchy visually: parent groups use restrained panels, child items use compact nested rows and a subtle connecting rail.
- Ordering is direct manipulation through drag-and-drop, with keyboard arrow controls as a fallback. Do not expose raw order numbers when the position can be shown spatially.
- Every builder must cover enabled, disabled, empty, dragging, unsaved, saving, saved, validation-error, and responsive states.
- Use neutral white and warm-gray surfaces, 6–10px radii, fine borders, and existing admin controls. Avoid gradients, oversized cards, and ornamental dashboard styling.
