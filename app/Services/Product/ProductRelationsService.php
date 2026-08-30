<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Models\ProductAttributeOptions;
use App\Models\ProductCharacteristics;
use App\Models\ProductFaqs;
use App\Models\ProductVideos;
use Illuminate\Support\Collection;
use RuntimeException;

class ProductRelationsService
{
    public function getCharacteristics(int $productId): Collection
    {
        return ProductCharacteristics::where('product_id', $productId)->get();
    }

    public function syncCharacteristics(int $productId, ?array $characteristics): void
    {
        $existing = ProductCharacteristics::where('product_id', $productId)->get();
        $requestedIds = [];

        foreach ($characteristics ?? [] as $characteristic) {
            $data = [
                'product_id' => $productId,
                'name' => $characteristic['name'],
                'value' => $characteristic['value'],
            ];

            if ($id = $this->recordId($characteristic)) {
                $record = $existing->firstWhere('id', $id);
                if (! $record) {
                    throw new RuntimeException("Incorrect product characteristic id: {$id}");
                }

                $record->update($data);
                $requestedIds[] = $id;
            } else {
                ProductCharacteristics::create($data);
            }
        }

        $existing->whereNotIn('id', $requestedIds)->each->delete();
    }

    public function getVideos(int $productId): Collection
    {
        return ProductVideos::where('product_id', $productId)->get();
    }

    public function syncVideos(int $productId, ?array $videos): void
    {
        $existing = ProductVideos::where('product_id', $productId)->get();
        $requestedIds = [];

        foreach ($videos ?? [] as $video) {
            $data = [
                'product_id' => $productId,
                'tab' => $video['tab'],
                'iframe' => $video['iframe'],
            ];

            if ($id = $this->recordId($video)) {
                $record = $existing->firstWhere('id', $id);
                if (! $record) {
                    throw new RuntimeException("Incorrect product video id: {$id}");
                }

                $record->update($data);
                $requestedIds[] = $id;
            } else {
                ProductVideos::create($data);
            }
        }

        $existing->whereNotIn('id', $requestedIds)->each->delete();
    }

    public function getFaqs(int $productId): Collection
    {
        return ProductFaqs::where('product_id', $productId)->get();
    }

    public function syncFaqs(int $productId, ?array $faqs): void
    {
        $existing = ProductFaqs::where('product_id', $productId)->get();
        $requestedIds = [];

        foreach ($faqs ?? [] as $faq) {
            $data = [
                'product_id' => $productId,
                'question' => $faq['question'],
                'answer' => $faq['answer'],
            ];

            if ($id = $this->recordId($faq)) {
                $record = $existing->firstWhere('id', $id);
                if (! $record) {
                    throw new RuntimeException("Incorrect product FAQ id: {$id}");
                }

                $record->update($data);
                $requestedIds[] = $id;
            } else {
                ProductFaqs::create($data);
            }
        }

        $existing->whereNotIn('id', $requestedIds)->each->delete();
    }

    public function syncAttributes(int $productId, ?array $attributes): void
    {
        $existing = ProductAttributeOptions::where('product_id', $productId)->get();
        $requestedIds = [];

        foreach ($attributes ?? [] as $attributeId => $options) {
            foreach ($options as $option) {
                $data = [
                    'product_id' => $productId,
                    'product_attribute_id' => (int) $attributeId,
                    'name' => $option['name'],
                    'price' => $option['price'],
                ];

                if ($id = $this->recordId($option)) {
                    $record = $existing->firstWhere('id', $id);
                    if (! $record || (int) $record->product_attribute_id !== (int) $attributeId) {
                        throw new RuntimeException("Incorrect product attribute option id: {$id}");
                    }

                    $record->update($data);
                    $requestedIds[] = $id;
                } else {
                    ProductAttributeOptions::create($data);
                }
            }
        }

        $existing->whereNotIn('id', $requestedIds)->each->delete();
    }

    public function syncColors(?array $colors, Product $product): void
    {
        $colorsToSync = [];

        foreach ($colors ?? [] as $color) {
            $colorsToSync[(int) $color['color_id']] = ['price' => $color['price']];
        }

        $product->colors()->sync($colorsToSync);
    }

    private function recordId(array $record): ?int
    {
        return isset($record['id']) && $record['id'] !== '' && $record['id'] !== null
            ? (int) $record['id']
            : null;
    }
}
