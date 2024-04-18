<?php

namespace App\Services\BlogPage;

use App\Services\BlogPage\DTO\EditBlogPageDTO;
use App\Models\BlogPageConfig;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;

class BlogPageService extends BaseService
{

    public function editBlogPage(EditBlogPageDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($request) {

            $existingConfig = $this->getConfigData();
            $dataToUpdate = [
                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeywords,
                'meta_tags' => $request->metaTags,
                'title' => $request->title,
            ];

            if( !is_null($existingConfig)){
                $existingConfig->update($dataToUpdate);
            } else {
                BlogPageConfig::create($dataToUpdate);
            }

            return ServiceActionResult::make(true, trans('admin.blog_page_edit_success'));
        });
    }

    public function getConfigData()
    {
        return BlogPageConfig::first();
    }

}
