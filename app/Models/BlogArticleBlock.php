<?php

namespace App\Models;

use App\DataClasses\BlogArticleBlockTypesDataClass;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BlogArticleBlock extends Model
{
    protected $casts = [
        'content' => 'array',
    ];

    protected $guarded = [];

    public function image(): Attribute
    {
        return Attribute::make(function () {
            if ($this->type_id !== BlogArticleBlockTypesDataClass::TYPE_IMAGE) {
                return null;
            }

            if ($this->content->image_path) {
                return Storage::url($this->content->image_path);
            }
            return null;
        });
    }

    public function content(): Attribute
    {
        return Attribute::make(
            get: function (string $value) {
                $value = json_decode($value, true);
                if ($this->type_id === BlogArticleBlockTypesDataClass::TYPE_IMAGE || $this->type_id === BlogArticleBlockTypesDataClass::TYPE_SLIDER) {

                    foreach ($value['images'] as &$imageContent) {
                        $imageContent['image_url'] = Storage::url($imageContent['image_path']);
                        $selectedProduct = isset($imageContent['product_id']) ? Product::find($imageContent['product_id']) : null;
                        if ($selectedProduct) {
                            $imageContent['selected_product'] = $selectedProduct;
                            $imageContent['selected_product']['text'] = $selectedProduct->name;
                        }
                    }

                    return $value;
                } else if($this->type_id === BlogArticleBlockTypesDataClass::TYPE_QUOTE) {

                    if (isset($value['quote_author_image_path'])) {
                        $value['quote_author_image_url'] = Storage::url($value['quote_author_image_path']);
                    }

                    return $value;
                } else if($this->type_id === BlogArticleBlockTypesDataClass::TYPE_SPONSOR) {

                    if (isset($value['sponsor_image_path'])) {
                        $value['sponsor_image_url'] = Storage::url($value['sponsor_image_path']);
                    }

                    return $value;
                } else {
                    return $value;
                }
            }
        );
    }
}
