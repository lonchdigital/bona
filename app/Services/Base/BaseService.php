<?php

namespace App\Services\Base;

use App\Models\Faqs;
use App\Models\User;
use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Throwable;

class BaseService
{
    /**
     * @throws Throwable
     */
    protected function coverWithDBTransaction(Closure $closure): ServiceActionResult
    {
        try {
            return DB::transaction($closure);
        } catch (Throwable $throwable) {
            $this->logCaughtException($throwable);

            if (config('app.debug')) {
                throw $throwable;
            }

            return ServiceActionResult::make(false, trans('common.action_unexpected_error'));
        }
    }

    /**
     * @throws Throwable
     */
    protected function coverWithDBTransactionWithoutResponse(Closure $closure): mixed
    {
        return DB::transaction($closure);
    }

    /**
     * @throws Throwable
     */
    public function coverWithTryCatch(Closure $closure): ServiceActionResult
    {
        try {
            $result = $closure();
        } catch (Throwable $throwable) {
            $this->logCaughtException($throwable);

            if (config('app.debug')) {
                throw $throwable;
            } else {
                return ServiceActionResult::make(false, trans('common.action_unexpected_error'));
            }
        }

        return $result;
    }

    protected function logCaughtException(Throwable $throwable): void
    {
        $callerFunctionName = debug_backtrace()[1]['function'];
        Log::error(
            get_class().'@'.$callerFunctionName.' '.$throwable->getMessage().PHP_EOL.$throwable->getTraceAsString()
        );
    }

    protected function getAuthUser(): User
    {
        return Auth::user();
    }

    protected function storeImage(string $path, UploadedFile $image, string $format, $quality = 70): void
    {
        $image = Image::make($image)->encode($format, $quality);
        Storage::disk(config('app.images_disk_default'))->put($path.'.'.$format, $image);
    }

    protected function deleteImage(?string $path): void
    {
        if (is_null($path)) {
            return;
        }

        // remove webp
        if (Storage::disk(config('app.images_disk_default'))->exists($path)) {
            Storage::disk(config('app.images_disk_default'))->delete($path);
        }

        // remove jpg
        $jpgPath = pathinfo($path, PATHINFO_DIRNAME).'/'.pathinfo($path, PATHINFO_FILENAME).'.jpg';
        if (Storage::disk(config('app.images_disk_default'))->exists($jpgPath)) {
            Storage::disk(config('app.images_disk_default'))->delete($jpgPath);
        }

        // remove png
        $jpgPath = pathinfo($path, PATHINFO_DIRNAME).'/'.pathinfo($path, PATHINFO_FILENAME).'.png';
        if (Storage::disk(config('app.images_disk_default'))->exists($jpgPath)) {
            Storage::disk(config('app.images_disk_default'))->delete($jpgPath);
        }
    }

    protected function storeAuthorAvatar(string $path, UploadedFile $image, string $format, $quality = 100): void
    {
        $image = Image::make($image)->fit(300, 300)->encode($format, $quality);
        Storage::disk(config('app.images_disk_default'))->put($path.'.'.$format, $image);
    }

    protected function syncFaqs(string $pageType, ?array $faqs): void
    {
        $existingFaqs = Faqs::where('page_type', $pageType)->get();
        if ($faqs) {
            foreach ($faqs as $faq) {
                $dataToUpdate = [
                    'page_type' => $pageType,
                    'question' => $faq['question'],
                    'answer' => $faq['answer'],
                ];

                if (isset($faq['id']) && $faq['id']) {
                    $existingFaq = $existingFaqs->where('id', $faq['id'])->first();
                    if (! $existingFaq) {
                        throw new \Exception('Incorrect faq id: '.$faq['id']);
                    }

                    $existingFaq->update($dataToUpdate);
                } else {
                    Faqs::create($dataToUpdate);
                }
            }
        }

        $existingFaqsInRequest = $faqs ? array_filter(array_column($faqs, 'id'), function ($item) {
            return $item !== null;
        }) : [];

        $faqsToDelete = $existingFaqs->whereNotIn('id', $existingFaqsInRequest);

        foreach ($faqsToDelete as $faqToDelete) {
            $faqToDelete->delete();
        }

    }

    protected function arraysAreEqual($arrayOne, $arrayTwo)
    {
        if (count($arrayOne) !== count($arrayTwo)) {
            return false;
        }

        // sort arrays by keys
        ksort($arrayOne);
        ksort($arrayTwo);

        // compare arrays
        return $arrayOne === $arrayTwo;
    }
}
