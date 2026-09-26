---
name: Bona configurator administration
description: Scoped extension of the incumbent admin; not a storefront design system.
colors:
  text: "#30343b"
  note: "#555f6b"
  border: "#d9dde3"
  surface: "#fff"
  focus: "#315ca4"
typography:
  body:
    fontFamily: "Overpass, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 400
    lineHeight: 1.5
  label:
    fontFamily: "Overpass, sans-serif"
    fontWeight: 600
  disclosure:
    fontSize: "17px"
    fontWeight: 600
spacing:
  small: "12px"
  normal: "16px"
  section: "24px"
rounded:
  field: "0.25rem"
---

# Configurator admin visual contract

## Overview

Existing operational admin, not a new visual identity. Reuse its shell, Bootstrap forms, typography and dark action buttons. This scope governs only the configurator administration views. Product and editing responsibilities are recorded in the root PRODUCT.md.

## Colors

Dark readable form text; quieter instructional text; light section borders and white image/table grounds. Warnings, errors and success notices reuse incumbent semantic alert styles. Focus is visible independently of status colour.

## Typography

Use the incumbent Overpass body and heading classes. Labels and disclosure summaries carry weight, not uppercase ornament. IDs, prices and table counts retain tabular number alignment. Long instructions stay within the implemented paragraph measure.

## Layout

The administration surface is capped at 1520px. Header and filters wrap. Editing has fields plus a bounded image/handle-position preview; below 767px these stack into one column. Coordinate inputs change from four to two columns. Table overflow stays within its responsive wrapper. Image/file inputs cannot force the document wider.

## Elevation & Depth

This extension adds no ornamental shadow system. Borders and spacing separate editable sections, persistence controls and history. Native/Bootstrap focus treatment remains functional.

## Shapes

Keep the incumbent compact rectangular forms and buttons. Photo previews use contain-fit, not a crop that would hide the product. The handle-position canvas is bounded and does not stretch the page.

## Components

- **List:** doors/handles navigation, filters, model identity/photo, publication/material status, edit action and pagination.
- **Variant disclosure:** catalogue colour, optional configuration, two image roles, coordinates and handle point. Disabled variants remain visibly identifiable.
- **Persistence controls:** save draft is separate from saved-preview and publication. Unsaved or rejected input disables publication and retains a navigation guard. Uploading disables save.
- **History:** restoration creates a draft, never silently publishes. Previous immutable media remains available.

## Do's and Don'ts

- Do preserve readable explicit labels and the existing admin language.
- Do explain validation failures beside the editing workflow.
- Do keep the product catalogue as the source of commercial data.
- Don't introduce consumer-style marketing cards or redesign the shell.
- Don't present unsaved controls as the published version.
- Don't promote pre-existing icon-font glyphs, local debug toolbar or missing local seed logo into new design rules.
