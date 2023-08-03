<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductCategory\CategoryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateCountOfProductsByCategoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly ?int $productId = null,
    ) { }

    /**
     * Execute the job.
     */
    public function handle(CategoryService $categoryService): void
    {
        if ($this->productId) {
            $categories = Product::findOrFail($this->productId)->categories;
        } else {
            $categories = Category::get();
        }

        $result = $categoryService->updateCountOfProductsByCategory($categories);
        Log::info(($result->isSuccess() ? '[SUCCESS] ' : '[FAIL] ') . $result->getMessage());
    }
}
