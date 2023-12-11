<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSeoText extends Model
{
    protected $guarded = [];

    static function updateProductSeoText(int $id, array $title, array $content)
    {
        $data = [];
        foreach ($title as $lang => $value) {
            $data[$lang]['title'] = $value;
        }

        foreach ($content as $lang => $value) {
            $data[$lang]['content'] = $value;
        }

        foreach ($data as $lang => $values) {
            ProductSeoText::updateOrCreate(
                [
                    'product_id' => $id,
                    'language' => $lang
                ],
                $values
            );
        }

    }

    static function deleteProductSeoText(int $product_id)
    {
        $existingTexts = ProductSeoText::where('product_id', $product_id)->get();

        if(count($existingTexts)) {
            foreach ($existingTexts as $existingText) {
                $existingText->delete();
            }
        }

    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
