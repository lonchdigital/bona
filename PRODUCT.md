# Bona Doors

Bona Doors is an existing Ukrainian/Russian door and hardware storefront with a shared administration panel. Preserve established public URLs, storefront navigation and the incumbent admin UI when extending it.

## Door configurator operating contract

Approved scope: manage prepared doors and handles in the existing admin, import the completed portion, and publish only checked materials. This is not a separate login or a replacement commerce system. Room scenes and wall-palette administration are outside this release.

- Catalogue product IDs own names, current prices, availability, colours and purchasable options. Configurator records hold presentation assets and geometry, not separate merchandise or editable sale prices.
- A model can have several prepared colour/option combinations. Unprepared catalogue shades remain absent until their materials are reviewed and published.
- Save, preview and publish are distinct operations. Saving or restoring history creates a draft; publication explicitly changes the public configurator. Private draft preview cannot add products to the cart.
- Existing prepared models are imported once without duplicates. Repeated import preserves manual administration changes. Images live in persistent, immutable shared storage across releases.
- Administrators need searchable doors/handles lists, category/status filters, colour and option linkage, photo replacement, positioning, ordering, visibility and revision history.
- The public configurator is a visual approximation, not a measured 3D design or a guarantee of hardware compatibility. Checkout recalculates current catalogue prices on the server.
- The complete objective is all interior-door models and their shades in production, editable through this admin, before proceeding to other door categories. Releasing a finished portion is only an intermediate milestone, not completion.

## Admin visual authority

Continue the existing Bootstrap-based admin shell and typography. Prioritize readable labels, compact operational tables and safe editing on desktop and mobile. Do not introduce a separate visual identity or redesign unrelated screens.
