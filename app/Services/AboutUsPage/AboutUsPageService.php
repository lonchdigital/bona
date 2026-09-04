<?php

namespace App\Services\AboutUsPage;

use App\Models\AboutUsConfig;
use App\Models\AboutUsFact;
use App\Models\AboutUsStep;
use App\Models\AboutUsTeamMember;
use App\Services\AboutUsPage\DTO\AboutUsPageEditDTO;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AboutUsPageService extends BaseService
{
    const DELIVERY_PAGE_IMAGES_FOLDER = 'about-us-page-images';

    public function editAboutUsPage(AboutUsPageEditDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($request) {

            $existingConfig = AboutUsConfig::first();
            $dataToUpdate = [
                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeyWords,
                'meta_tags' => $request->metaTags,
                'title' => $request->title,
                'description' => $request->description,
                'button_text' => $request->buttonText,
                'button_url' => $request->buttonUrl,
                'iframe' => $request->iframe,

                'facts_title' => $request->factsTitle,
                'history_title' => $request->historyTitle,
                'history_text' => $request->historyText,
                'steps_title' => $request->stepsTitle,
                'team_title' => $request->teamTitle,
                'cta_title' => $request->ctaTitle,
                'cta_text' => $request->ctaText,
                'cta_button_text' => $request->ctaButtonText,
                'cta_button_url' => $request->ctaButtonUrl,
            ];

            $imagesToDelete = [];
            $deliveryImage = null;
            if (! is_null($request->image)) {
                $imagesToDelete[] = $existingConfig?->image;

                $newImagePath = self::DELIVERY_PAGE_IMAGES_FOLDER.'/'.sha1(time()).'_'.Str::random(10);
                $dataToUpdate['image'] = $newImagePath.'.webp';

                $deliveryImage['image'] = $request->image;
                $deliveryImage['path'] = $newImagePath;
            }

            if (! is_null($deliveryImage)) {
                $this->storeImage($deliveryImage['path'], $deliveryImage['image'], 'webp');
                $this->storeImage($deliveryImage['path'], $deliveryImage['image'], 'jpg');
            }

            foreach ($imagesToDelete as $imageToDelete) {
                if (! is_null($imageToDelete)) {
                    $this->deleteImage($imageToDelete);
                }
            }

            if ($request->imageDeleted && $existingConfig?->image) {
                $this->deleteImage($existingConfig->image);
                $dataToUpdate['image'] = null;
            }

            if (! is_null($existingConfig)) {
                $existingConfig->update($dataToUpdate);
            } else {
                AboutUsConfig::create($dataToUpdate);
            }

            $this->syncFacts($request->facts);
            $this->syncSteps($request->steps);
            $this->syncTeamMembers($request->teamMembers);

            return ServiceActionResult::make(true, trans('admin.about_us_edit_success'));
        });
    }

    public function getAboutUsConfig(): ?AboutUsConfig
    {
        return AboutUsConfig::first();
    }

    public function getFacts(): Collection
    {
        return AboutUsFact::orderBy('sort_order')->orderBy('id')->get();
    }

    public function getSteps(): Collection
    {
        return AboutUsStep::orderBy('sort_order')->orderBy('id')->get();
    }

    public function getTeamMembers(): Collection
    {
        return AboutUsTeamMember::orderBy('sort_order')->orderBy('id')->get();
    }

    /**
     * Rows missing from the form are removed, so the form is the single source
     * of truth for each block. A null means the form did not carry the block
     * at all, which leaves whatever is stored untouched.
     */
    private function syncFacts(?array $facts): void
    {
        if ($facts === null) {
            return;
        }

        $keptIds = [];

        foreach ($facts as $index => $fact) {
            $value = trim((string) ($fact['value'] ?? ''));

            if ($value === '') {
                continue;
            }

            $data = [
                'value' => $value,
                'label' => $fact['label'] ?? null,
                'sort_order' => $index,
            ];

            $keptIds[] = $this->storeRow(AboutUsFact::class, $fact['id'] ?? null, $data);
        }

        AboutUsFact::whereNotIn('id', $keptIds)->delete();
    }

    private function syncSteps(?array $steps): void
    {
        if ($steps === null) {
            return;
        }

        $keptIds = [];

        foreach ($steps as $index => $step) {
            if (! $this->hasTranslation($step['title'] ?? null)) {
                continue;
            }

            $data = [
                'title' => $step['title'],
                'text' => $step['text'] ?? null,
                'sort_order' => $index,
            ];

            $keptIds[] = $this->storeRow(AboutUsStep::class, $step['id'] ?? null, $data);
        }

        AboutUsStep::whereNotIn('id', $keptIds)->delete();
    }

    private function syncTeamMembers(?array $members): void
    {
        if ($members === null) {
            return;
        }

        $existing = AboutUsTeamMember::get();
        $keptIds = [];
        $imagesToDelete = [];

        foreach ($members as $index => $member) {
            if (! $this->hasTranslation($member['name'] ?? null)) {
                continue;
            }

            $data = [
                'name' => $member['name'],
                'role' => $member['role'] ?? null,
                'experience' => $member['experience'] ?? null,
                'quote' => $member['quote'] ?? null,
                'sort_order' => $index,
            ];

            $photo = $member['photo'] ?? null;

            if ($photo instanceof UploadedFile) {
                $path = self::DELIVERY_PAGE_IMAGES_FOLDER.'/'.sha1(microtime(true)).'_'.Str::random(10);
                $this->storeAuthorAvatar($path, $photo, 'webp');
                $this->storeAuthorAvatar($path, $photo, 'jpg');
                $data['photo_path'] = $path.'.webp';

                $previous = $existing->firstWhere('id', (int) ($member['id'] ?? 0))?->photo_path;

                if ($previous) {
                    $imagesToDelete[] = $previous;
                }
            }

            $keptIds[] = $this->storeRow(AboutUsTeamMember::class, $member['id'] ?? null, $data);
        }

        foreach ($existing->whereNotIn('id', $keptIds) as $memberToDelete) {
            if ($memberToDelete->photo_path) {
                $imagesToDelete[] = $memberToDelete->photo_path;
            }

            $memberToDelete->delete();
        }

        foreach ($imagesToDelete as $imagePath) {
            $this->deleteImage($imagePath);
        }
    }

    private function storeRow(string $model, mixed $id, array $data): int
    {
        if ($id && $row = $model::find((int) $id)) {
            $row->update($data);

            return $row->id;
        }

        return $model::create($data)->id;
    }

    /**
     * A repeatable row counts as filled in when at least one language carries
     * something; an empty row is simply dropped.
     */
    private function hasTranslation(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        foreach ($value as $translation) {
            if (trim((string) $translation) !== '') {
                return true;
            }
        }

        return false;
    }
}
