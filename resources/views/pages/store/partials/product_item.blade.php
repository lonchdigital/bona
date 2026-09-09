<x-store.product-card
    class="art-product-item"
    :product="$product"
    :base-currency="$baseCurrency"
    :selected-color-slugs="$selectedColorSlugs ?? data_get($filtersData ?? [], 'color', [])"
    variant="catalog"
/>
