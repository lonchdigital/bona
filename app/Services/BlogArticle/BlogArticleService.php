<?php

namespace App\Services\BlogArticle;

use App\DataClasses\BlogArticleBlockTypesDataClass;
use App\Models\BlogArticle;
use App\Models\BlogArticleBlock;
use App\Models\BlogCategory;
use App\Models\User;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\BlogArticle\DTO\EditBlogArticleDTO;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class BlogArticleService extends BaseService
{
    const BLOG_ARTICLE_IMAGES_FOLDER = 'blog-article-images';

    public function getBlogArticlesListPaginated()
    {
        return BlogArticle::paginate(config('domain.blog_items_per_page'));
    }

    public function getLatestArticlesExceptCurrent(int $currentArticleId)
    {
        return BlogArticle::latest()->limit(3)->whereNot('id', $currentArticleId)->get();
    }

    public function getLatestArticles(int $count)
    {
        return BlogArticle::latest()->limit($count)->get();
    }

    public function getBlogArticlesByCategoryListPaginated(BlogCategory $blogCategory)
    {
        return BlogArticle::where('blog_category_id', $blogCategory->id)->paginate(config('domain.items_per_page'));
    }

    public function createBlogArticle(EditBlogArticleDTO $request, User $creator): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($request, $creator) {

            $heroImagePath = self::BLOG_ARTICLE_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10);
            $this->storeImage($heroImagePath, $request->heroImage, 'webp');
            $this->storeImage($heroImagePath, $request->heroImage, 'jpg');

            $article = BlogArticle::create([
                'creator_id' => $creator->id,
                'name' => $request->name,
                'slug' => $request->slug,
                'preview_text' => $request->previewText,
                'hero_image_path' => $heroImagePath . '.webp',
                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeywords,
                'meta_tags' => $request->metaTags,
            ]);

            if ($request->blocks) {
                foreach ($request->blocks as $block) {
                    if ($block['type_id'] == BlogArticleBlockTypesDataClass::TYPE_TEXT) {
                        $this->handleStoreTextBlock($article, $block);
                    } elseif ($block['type_id'] == BlogArticleBlockTypesDataClass::TYPE_IMAGE) {
                        $this->handleStoreBlockWithImageWithTooltip($article, $block);
                    } elseif ($block['type_id'] == BlogArticleBlockTypesDataClass::TYPE_QUOTE) {
                        $this->handleStoreQuoteBlock($article, $block);
                    } elseif ($block['type_id'] == BlogArticleBlockTypesDataClass::TYPE_SPONSOR) {
                        $this->handleStoreSponsorBlock($article, $block);
                    } elseif ($block['type_id'] == BlogArticleBlockTypesDataClass::TYPE_VIDEO) {
                        $this->handleStoreVideoBlock($article, $block);
                    } elseif ($block['type_id'] == BlogArticleBlockTypesDataClass::TYPE_SLIDER) {
                        $this->handleStoreBlockWithImageWithTooltip($article, $block);
                    } elseif ($block['type_id'] == BlogArticleBlockTypesDataClass::TYPE_QUESTIONS_AND_ANSWERS) {
                        $this->handleStoreQuestionsAndAnswersBlock($article, $block);
                    }
                }
            }
            return ServiceActionResult::make(true, trans('admin.blog_article_create_success'));
        });
    }

    public function editBlogArticle(BlogArticle $article, EditBlogArticleDTO $request, User $creator): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($article, $request, $creator) {
            $existingBlocks = $article->blocks;
            $imagesToDelete = [];

            $fieldsToUpdate = [
                'name' => $request->name,
                'slug' => $request->slug,
                'preview_text' => $request->previewText,
                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeywords,
                'meta_tags' => $request->metaTags,
            ];

            if ($request->heroImage) {
                $heroImagePath = self::BLOG_ARTICLE_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10);

                $this->storeImage($heroImagePath, $request->heroImage, 'webp');
                $this->storeImage($heroImagePath, $request->heroImage, 'jpg');

                $fieldsToUpdate['hero_image_path'] = $heroImagePath . '.webp';
                $imagesToDelete[] = $article->hero_image_path;
            }

            $article->update($fieldsToUpdate);

            if ($request->blocks) {
                foreach ($request->blocks as $blockData) {
                    $blockModel = null;
                    if (isset($blockData['id'])) {
                        $blockModel = $article->blocks->where('id', $blockData['id'])->first();
                        if (!$blockModel) {
                            throw new \Exception('Block with id: ' . $blockData['id'] . 'is not exists on this article');
                        }
                    }

                    if ($blockData['type_id'] == BlogArticleBlockTypesDataClass::TYPE_TEXT) {
                        if ($blockModel) {
                            $this->handleEditTextBlock($blockModel, $blockData);
                        } else {
                            $this->handleStoreTextBlock($article, $blockData);
                        }
                    } else if ($blockData['type_id'] == BlogArticleBlockTypesDataClass::TYPE_IMAGE ||
                        $blockData['type_id'] == BlogArticleBlockTypesDataClass::TYPE_SLIDER) {
                        if ($blockModel) {
                            foreach ($blockData['images'] as $index => $image) {
                                if (isset($image['image']) && isset($blockModel->content['images'][$index])) {
                                    $imagesToDelete[] = $blockModel->content['images'][$index]['image_path'];
                                }
                            }
                            $this->handleEditBlockWithImageWithTooltip($blockModel, $blockData);
                        } else {
                            $this->handleStoreBlockWithImageWithTooltip($article, $blockData);
                        }
                    } else if ($blockData['type_id'] == BlogArticleBlockTypesDataClass::TYPE_QUOTE) {
                        if ($blockModel) {
                            if (($blockData['quote_author_image_deleted'] || isset($blockData['quote_author_image'])) && isset($blockModel->content['quote_author_image_path'])) {
                                $imagesToDelete[] = $blockModel->content['quote_author_image_path'];
                            }
                            $this->handleEditQuoteBlock($blockModel, $blockData);
                        } else {
                            $this->handleStoreQuoteBlock($article, $blockData);
                        }
                    } else if ($blockData['type_id'] == BlogArticleBlockTypesDataClass::TYPE_SPONSOR) {
                        if ($blockModel) {
                            if (isset($blockData['sponsor_image']) && isset($blockModel->content['sponsor_image_path'])) {
                                $imagesToDelete[] = $blockModel->content['sponsor_image_path'];
                            }
                            $this->handleEditSponsorBlock($blockModel, $blockData);
                        } else {
                            $this->handleStoreSponsorBlock($article, $blockData);
                        }
                    } else if ($blockData['type_id'] == BlogArticleBlockTypesDataClass::TYPE_VIDEO) {
                        if ($blockModel) {
                            $this->handleEditVideoBlock($blockModel, $blockData);
                        } else {
                            $this->handleStoreVideoBlock($article, $blockData);
                        }
                    } else if ($blockData['type_id'] == BlogArticleBlockTypesDataClass::TYPE_QUESTIONS_AND_ANSWERS) {
                        if ($blockModel) {
                            $this->handleEditQuestionsAndAnswersBlock($blockModel, $blockData);
                        } else {
                            $this->handleStoreQuestionsAndAnswersBlock($article, $blockData);
                        }
                    }

                }
            }

            if ($request->blocks) {
                $blocksInRequest = array_filter(array_column($request->blocks, 'id'), function ($id) {
                    return !!$id;
                });
            } else {
                $blocksInRequest = [];
            }


            $existingBlockToDelete = $existingBlocks->whereNotIn('id', $blocksInRequest);

            foreach ($existingBlockToDelete as $blockToDelete) {
                if ($blockToDelete->type_id === BlogArticleBlockTypesDataClass::TYPE_IMAGE ||
                    $blockToDelete->type_id === BlogArticleBlockTypesDataClass::TYPE_SLIDER) {
                    foreach ($blockToDelete->content['images'] as $image) {
                        if (isset($image['image_path'])) {
                            $imagesToDelete[] = $image['image_path'];
                        }
                    }
                } else if ($blockToDelete->type_id === BlogArticleBlockTypesDataClass::TYPE_QUOTE) {
                    if (isset($blockToDelete->content['quote_author_image_path'])) {
                        $imagesToDelete[] = $blockToDelete->content['quote_author_image_path'];
                    }
                } else if ($blockToDelete->type_id === BlogArticleBlockTypesDataClass::TYPE_SPONSOR) {
                    if (isset($blockToDelete->content['sponsor_image_path'])) {
                        $imagesToDelete[] = $blockToDelete->content['sponsor_image_path'];
                    }
                }

                $blockToDelete->delete();
            }

            foreach ($imagesToDelete as $imagePath) {
                $this->deleteImage($imagePath);
            }

            return ServiceActionResult::make(true, trans('admin.blog_article_edit_success'));
        });
    }

    public function deleteBlogArticle(BlogArticle $blogArticle): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($blogArticle) {
            $imagesToDelete = [];

            $imagesToDelete[] = $blogArticle->hero_image_path;

            foreach ($blogArticle->blocks as $block) {
                if ($block->type_id === BlogArticleBlockTypesDataClass::TYPE_IMAGE ||
                    $block->type_id === BlogArticleBlockTypesDataClass::TYPE_SLIDER) {
                    foreach ($block->content['images'] as $image) {
                        $imagesToDelete[] = $image['image_path'];
                    }
                } elseif ($block->type_id === BlogArticleBlockTypesDataClass::TYPE_QUOTE) {
                    if (isset($block->content['quote_author_image_path'])) {
                        $imagesToDelete[] = $block->content['quote_author_image_path'];
                    }
                } elseif ($block->type_id === BlogArticleBlockTypesDataClass::TYPE_SPONSOR) {
                    if (isset($block->content['sponsor_image_path'])) {
                        $imagesToDelete[] = $block->content['sponsor_image_path'];
                    }
                }
            }

            $blogArticle->blocks()->delete();
            $blogArticle->delete();

            foreach ($imagesToDelete as $imageToDelete) {
                $this->deleteImage($imageToDelete);
            }

            return ServiceActionResult::make(true, trans('admin.blog_article_delete_success'));
        });
    }

    private function handleStoreTextBlock(BlogArticle $article, array $block): void
    {
        BlogArticleBlock::create([
            'type_id' => $block['type_id'],
            'blog_article_id' => $article->id,
            'content' => Arr::except($block, ['type_id', 'id']),
        ]);
    }

    private function handleEditTextBlock(BlogArticleBlock $articleBlock, array $block): void
    {
        $articleBlock->update([
            'content' =>  Arr::except($block, ['type_id', 'id']),
        ]);
    }

    private function prepareContentForQuoteBlock(array $block): array
    {
        $storeQuoteAuthor = false;
        foreach ($block['quote_author'] as $quoteAuthorOnLanguage) {
            if ($quoteAuthorOnLanguage) {
                $storeQuoteAuthor = true;
            }
        }

        $storeQuoteAuthorPosition = false;
        foreach ($block['quote_author_position'] as $quoteAuthorPositionOnLanguage) {
            if ($quoteAuthorPositionOnLanguage) {
                $storeQuoteAuthorPosition = true;
            }
        }

        $fieldsToStore = [
            'quote',
        ];

        if ($storeQuoteAuthor) {
            $fieldsToStore[] = 'quote_author';
        }

        if ($storeQuoteAuthorPosition) {
            $fieldsToStore[] = 'quote_author_position';
        }

        $content = Arr::only($block, $fieldsToStore);

        if (isset($block['quote_author_image'])) {
            $imagePath = self::BLOG_ARTICLE_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '.jpg';
            $this->storeArticleImage($imagePath, $block['quote_author_image']);
            $content['quote_author_image_path'] = $imagePath;
        }

        return $content;
    }

    private function handleStoreQuoteBlock(BlogArticle $article, array $block): void
    {
        $content = $this->prepareContentForQuoteBlock($block);

        BlogArticleBlock::create([
            'type_id' => $block['type_id'],
            'blog_article_id' => $article->id,
            'content' => $content,
        ]);
    }

    private function handleEditQuoteBlock(BlogArticleBlock $articleBlock, array $block): void
    {
        $content = $this->prepareContentForQuoteBlock($block);

        if (!$block['quote_author_image_deleted'] && !isset($block['quote_author_image']) && isset($articleBlock->content['quote_author_image_path'])) {
            $content['quote_author_image_path'] = $articleBlock->content['quote_author_image_path'];
        }

        $articleBlock->update([
            'content' => $content,
        ]);
    }

    private function handleStoreSponsorBlock(BlogArticle $article, array $block): void
    {
        $imagePath = self::BLOG_ARTICLE_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '.jpg';
        $this->storeArticleImage($imagePath, $block['sponsor_image']);

        $block['sponsor_image_path'] = $imagePath;

        BlogArticleBlock::create([
            'type_id' => $block['type_id'],
            'blog_article_id' => $article->id,
            'content' => Arr::only($block, [
                'sponsor_text',
                'sponsor_link',
                'sponsor_image_path'
            ]),
        ]);
    }

    private function handleEditSponsorBlock(BlogArticleBlock $articleBlock, array $block): void
    {
        if (isset($block['sponsor_image'])) {
            $imagePath = self::BLOG_ARTICLE_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '.jpg';
            $this->storeArticleImage($imagePath, $block['sponsor_image']);
            $block['sponsor_image_path'] = $imagePath;
        } else {
            if (isset($articleBlock->content['sponsor_image_path'])) {
                $block['sponsor_image_path'] = $articleBlock->content['sponsor_image_path'];
            }
        }

        $articleBlock->update([
            'content' => Arr::only($block, [
                'sponsor_text',
                'sponsor_link',
                'sponsor_image_path'
            ]),
        ]);
    }

    private function getYouTubeVideoId(string $link): string
    {
        if (str_contains($link, 'youtube.com') && !str_contains($link, 'embed')) {
            $result = [];
            parse_str(parse_url($link)['query'], $result);
            return $result['v'];
        } else if (str_contains($link, 'youtu.be')) {
            return explode('youtu.be/', $link)[1];
        } else if (str_contains($link, 'youtube.com') && str_contains($link, 'embed')) {
            return explode('/embed/', $link)[1];
        }

        return '';
    }

    private function handleStoreVideoBlock(BlogArticle $article, array $block): void
    {
        $videoLink = 'https://www.youtube.com/embed/' . $this->getYouTubeVideoId($block['video_link']);

        BlogArticleBlock::create([
            'type_id' => $block['type_id'],
            'blog_article_id' => $article->id,
            'content' => [
                'video_link' => $videoLink,
            ],
        ]);
    }

    private function handleEditVideoBlock(BlogArticleBlock $articleBlock, array $block): void
    {
        $videoLink = 'https://www.youtube.com/embed/' . $this->getYouTubeVideoId($block['video_link']);

        $articleBlock->update([
            'content' => [
                'video_link' => $videoLink,
            ],
        ]);
    }

    private function prepareContentForBlockWithImageWithTooltip(array $block): array
    {
        $content = [];

        foreach ($block['images'] as $image) {

            $imageData = [];

            if (isset($image['image'])) {
                $imagePath = self::BLOG_ARTICLE_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '.jpg';
                $this->storeArticleImage($imagePath, $image['image']);
                $imageData['image_path'] = $imagePath;
            }

            $position = [];

            if (isset($image['top'])) {
                $position['top'] = $image['top'];
            }

            if (isset($image['left'])) {
                $position['left'] = $image['left'];
            }

            if (count($position)) {
                $imageData['position'] = $position;
            }

            if (isset($image['product_id'])) {
                $imageData['product_id'] = $image['product_id'];
            }

            $content[] = $imageData;
        }

        return  $content;
    }

    private function handleStoreBlockWithImageWithTooltip(BlogArticle $article, array $block): void
    {
        $content = $this->prepareContentForBlockWithImageWithTooltip($block);

        BlogArticleBlock::create([
            'type_id' => $block['type_id'],
            'blog_article_id' => $article->id,
            'content' => ['images' => $content],
        ]);
    }

    private function handleEditBlockWithImageWithTooltip(BlogArticleBlock $articleBlock, array $block): void
    {
        $content = $this->prepareContentForBlockWithImageWithTooltip($block);

        foreach ($content as $index => $image) {
            if (!isset($content[$index]['image_path'])) {
                $content[$index]['image_path'] = $articleBlock->content['images'][$index]['image_path'];
            }
        }

        $articleBlock->update([
            'content' => ['images' => $content],
        ]);
    }

    private function handleStoreQuestionsAndAnswersBlock(BlogArticle $article, array $block): void
    {
        BlogArticleBlock::create([
            'type_id' => $block['type_id'],
            'blog_article_id' => $article->id,
            'content' => Arr::only($block, ['questions']),
        ]);
    }

    private function handleEditQuestionsAndAnswersBlock(BlogArticleBlock $articleBlock, array $block): void
    {
        $articleBlock->update([
            'content' => Arr::only($block, ['questions']),
        ]);
    }

    private function storeArticleImage(string $path, UploadedFile $image): void
    {
        $image = Image::make($image)->encode('jpg', 100);

        Storage::disk(config('app.images_disk_default'))->put($path, $image);
    }

    private function deleteArticleImage(string $path): void
    {
        if (Storage::disk(config('app.images_disk_default'))->exists($path)) {
            Storage::disk(config('app.images_disk_default'))->delete($path);
        }
    }
}
