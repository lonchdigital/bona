<?php

namespace App\Services\Author;

use App\Models\Author;
use App\Models\AuthorCertificate;
use App\Models\User;
use App\Services\Author\DTO\EditAuthorDTO;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class AuthorService extends BaseService
{
    public const AUTHOR_IMAGES_FOLDER = 'author-images';

    public function getAuthors(): Collection
    {
        return Author::orderBy('id')->get();
    }

    public function getAuthorsPaginated(): LengthAwarePaginator
    {
        return Author::with('creator')->orderBy('id')->paginate(config('domain.items_per_page'));
    }

    /**
     * The author printed under blog articles. Until the site grows a second
     * one, that is simply the first author on record.
     */
    public function getDefaultAuthor(): ?Author
    {
        return Author::orderBy('id')->first();
    }

    public function createAuthor(EditAuthorDTO $request, User $creator): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($request, $creator) {
            $author = Author::create($this->buildAuthorData($request) + [
                'creator_id' => $creator->id,
                'photo_path' => $request->photo ? $this->storeAuthorImage($request->photo) : null,
            ]);

            $this->syncCertificates($author, $request->certificates);

            return ServiceActionResult::make(true, trans('admin.author_create_success'));
        });
    }

    public function updateAuthor(Author $author, EditAuthorDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($author, $request) {
            $authorData = $this->buildAuthorData($request);

            $previousPhotoPath = null;

            if ($request->photo) {
                $previousPhotoPath = $author->photo_path;
                $authorData['photo_path'] = $this->storeAuthorImage($request->photo);
            }

            $author->update($authorData);

            $this->syncCertificates($author, $request->certificates);

            if ($previousPhotoPath) {
                $this->deleteImage($previousPhotoPath);
            }

            return ServiceActionResult::make(true, trans('admin.author_edit_success'));
        });
    }

    public function deleteAuthor(Author $author): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($author) {
            $imagesToDelete = $author->certificates->pluck('image_path')->all();

            if ($author->photo_path) {
                $imagesToDelete[] = $author->photo_path;
            }

            $author->certificates()->delete();
            $author->delete();

            foreach ($imagesToDelete as $imagePath) {
                $this->deleteImage($imagePath);
            }

            return ServiceActionResult::make(true, trans('admin.author_delete_success'));
        });
    }

    private function buildAuthorData(EditAuthorDTO $request): array
    {
        return [
            'name' => $request->name,
            'slug' => $request->slug,
            'job_title' => $request->jobTitle,
            'short_description' => $request->shortDescription,
            'biography' => $request->biography,
            'instagram_url' => $request->instagramUrl,
            'facebook_url' => $request->facebookUrl,
            'linkedin_url' => $request->linkedinUrl,
            'meta_title' => $request->metaTitle,
            'meta_description' => $request->metaDescription,
            'meta_keywords' => $request->metaKeywords,
        ];
    }

    /**
     * Rows missing from the request are removed, so the form is the single
     * source of truth for the gallery.
     */
    private function syncCertificates(Author $author, ?array $certificates): void
    {
        $existingCertificates = $author->certificates()->get();
        $imagesToDelete = [];
        $keptIds = [];

        foreach ($certificates ?? [] as $index => $certificate) {
            $certificateModel = null;

            if (!empty($certificate['id'])) {
                $certificateModel = $existingCertificates->firstWhere('id', (int) $certificate['id']);

                if (!$certificateModel) {
                    throw new \Exception('Certificate ' . $certificate['id'] . ' does not belong to this author');
                }
            }

            $data = [
                'title' => $certificate['title'] ?? null,
                'issuer' => $certificate['issuer'] ?? null,
                'issued_year' => $certificate['issued_year'] ?? null,
                'sort_order' => $index,
            ];

            $image = $certificate['image'] ?? null;

            if ($image instanceof UploadedFile) {
                $data['image_path'] = $this->storeAuthorImage($image);

                if ($certificateModel?->image_path) {
                    $imagesToDelete[] = $certificateModel->image_path;
                }
            }

            if ($certificateModel) {
                $certificateModel->update($data);
                $keptIds[] = $certificateModel->id;

                continue;
            }

            if (!isset($data['image_path'])) {
                // A new row without a picture is nothing but an empty slot.
                continue;
            }

            $keptIds[] = AuthorCertificate::create($data + ['author_id' => $author->id])->id;
        }

        foreach ($existingCertificates->whereNotIn('id', $keptIds) as $certificateToDelete) {
            $imagesToDelete[] = $certificateToDelete->image_path;
            $certificateToDelete->delete();
        }

        foreach ($imagesToDelete as $imagePath) {
            $this->deleteImage($imagePath);
        }
    }

    /**
     * Stored twice, matching the rest of the project: webp for the site, jpg as
     * the companion used by messengers and older clients.
     */
    private function storeAuthorImage(UploadedFile $image): string
    {
        $path = self::AUTHOR_IMAGES_FOLDER . '/' . sha1(microtime(true)) . '_' . Str::random(10);

        $this->storeImage($path, $image, 'webp');
        $this->storeImage($path, $image, 'jpg');

        return $path . '.webp';
    }
}
