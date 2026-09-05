<?php

namespace Tests\Unit;

use App\Support\Product\ProductPageDefaults;
use PHPUnit\Framework\TestCase;

class ProductPageDefaultsTest extends TestCase
{
    public function test_default_product_sections_have_stable_unique_ids_and_both_languages(): void
    {
        $blocks = ProductPageDefaults::blocks();
        $ids = array_column($blocks, 'id');

        $this->assertSame($ids, ProductPageDefaults::ids());
        $this->assertCount(count(array_unique($ids)), $ids);
        $this->assertSame(['benefits', 'full_kit', 'journey', 'installments'], array_column($blocks, 'type'));

        foreach ($blocks as $block) {
            $this->assertNotEmpty($block['title']['uk']);
            $this->assertNotEmpty($block['title']['ru']);
        }
    }
}
