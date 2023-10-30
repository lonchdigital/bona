<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoText extends Model
{
    protected $guarded = [];

    static function updateSeoText(string $pageType, array $title, array $content)
    {
        $data = [];
        foreach ($title as $lang => $value) {
            $data[$lang]['title'] = $value;
        }

        foreach ($content as $lang => $value) {
            $data[$lang]['content'] = $value;
        }

        foreach ($data as $lang => $values) {
            SeoText::updateOrCreate(
                [
                    'page_type' => $pageType,
                    'language' => $lang
                ],
                $values
            );
        }

    }

}
