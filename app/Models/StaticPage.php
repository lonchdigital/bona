<?php

namespace App\Models;

use App\DataClasses\StaticPageTypesDataClass;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

class StaticPage extends Model implements Sitemapable
{
    protected $guarded = [];

    public function content()
    {
        return $this->hasMany(StaticPageContent::class);
    }

    public function toSitemapTag(): Url | string | array
    {
        $type = StaticPageTypesDataClass::get($this->type_id);

        if ($type !== null && isset($type['slug'])) {
            return route('store.static-page.page', ['staticPageSlug' => $type['slug']]);
        }

        return '';
    }
}
