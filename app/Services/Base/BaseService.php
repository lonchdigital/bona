<?php

namespace App\Services\Base;

use Closure;
use App\Models\User;
use App\Models\Faqs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class BaseService
{
    /**
     * @throws Throwable
     */
    protected function coverWithDBTransaction(Closure $closure): ServiceActionResult
    {
        try {
            DB::beginTransaction();

            $result = $closure();

            DB::commit();
        } catch (\Throwable $throwable) {
            DB::rollback();
            $this->logCaughtException($throwable);

            if (config('app.debug')) {
                throw $throwable;
            } else {
                return ServiceActionResult::make(false, trans('common.action_unexpected_error'));
            }
        }

        return $result;
    }

    /**
     * @throws Throwable
     */
    protected function coverWithDBTransactionWithoutResponse(Closure $closure): mixed
    {
        try {
            DB::beginTransaction();

            $result = $closure();

            DB::commit();
        } catch (\Throwable $throwable) {
            DB::rollback();
            $this->logCaughtException($throwable);
            throw $throwable;
        }

        return $result;
    }

    /**
     * @throws Throwable
     */
    public function coverWithTryCatch(Closure $closure): ServiceActionResult
    {
        try {
            $result = $closure();
        } catch (\Throwable $throwable) {
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
            get_class() . '@' . $callerFunctionName . ' ' . $throwable->getMessage() . PHP_EOL . $throwable->getTraceAsString()
        );
    }

    protected function getAuthUser(): User
    {
        return Auth::user();
    }


    protected function storeImage(string $path, UploadedFile $image, string $format = 'jpg'): void
    {
        $image = Image::make($image)->encode($format, 100);
        Storage::disk(config('app.images_disk_default'))->put($path, $image);
    }

    protected function deleteImage(string $path): void
    {
        if (Storage::disk(config('app.images_disk_default'))->exists($path)) {
            Storage::disk(config('app.images_disk_default'))->delete($path);
        }
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
                    if (!$existingFaq) {
                        throw new \Exception('Incorrect faq id: ' . $faq['id']);
                    }

                    $existingFaq->update($dataToUpdate);
                } else {
                    Faqs::create($dataToUpdate);
                }
            }
        }

        $existingFaqsInRequest = $faqs ? array_filter(array_column($faqs, 'id'), function ($item) {
            return $item !== null;
        }): [];

        $faqsToDelete = $existingFaqs->whereNotIn('id', $existingFaqsInRequest);

        foreach ($faqsToDelete as $faqToDelete) {
            $faqToDelete->delete();
        }

    }
}
