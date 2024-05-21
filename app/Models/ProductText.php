<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductText extends Model
{
    protected $guarded = [];

    static function updateProductShortText(int $id, array $content)
    {
        $data = [];

        foreach ($content as $lang => $value) {
            $data[$lang]['short_content'] = $value;
        }

        foreach ($data as $lang => $values) {
            ProductText::updateOrCreate(
                [
                    'product_id' => $id,
                    'language' => $lang
                ],
                $values
            );
        }

    }

    static function updateProductText(int $id, array $content)
    {
        $data = [];

        foreach ($content as $lang => $value) {
            $data[$lang]['content'] = $value;
        }

        foreach ($data as $lang => $values) {
            ProductText::updateOrCreate(
                [
                    'product_id' => $id,
                    'language' => $lang
                ],
                $values
            );
        }

    }

    static function deleteProductText(int $product_id)
    {
        $existingTexts = ProductText::where('product_id', $product_id)->get();

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
