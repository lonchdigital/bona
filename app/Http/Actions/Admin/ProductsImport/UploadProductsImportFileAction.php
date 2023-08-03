<?php

namespace App\Http\Actions\Admin\ProductsImport;

use App\Models\ProductType;
use App\Http\Actions\Admin\BaseAction;
use App\Http\Resources\BaseActionResource;
use App\Services\Product\ProductImportUploadService;
use App\Http\Requests\Admin\ProductsImport\UploadProductsImportFileRequest;

class UploadProductsImportFileAction extends BaseAction
{
    public function __invoke(ProductType $productType, UploadProductsImportFileRequest $request, ProductImportUploadService $productImportService)
    {
        $result = $productImportService->importProductsFromFile($productType, $request->toDTO());

        if ($result['isSuccess']) {
            return BaseActionResource::make([
                'success' => true,
                'message' => trans('admin.products_import_upload_success'),
                'redirect_to' => route('admin.products-import.list', ['productType' => $productType->id]),
            ]);
        } else {

            $beatifiedErrors = [];

            if ($result['errorsByRow'] !== null) {
                foreach ($result['errorsByRow'] as $row => $errorsByRow) {
                    $beatifiedErrors[] = trans('admin.products_import_invalid_data_in_row', ['ROW' => $row]);
                    $beatifiedErrors = array_merge($beatifiedErrors, $errorsByRow);
                }

                if (!$result['allErrorsShowed']) {
                    $beatifiedErrors[] = trans('admin.products_import_and_others');
                }
            } elseif ($result['singleError'] !== null) {
                $beatifiedErrors[] = $result['singleError'];
            }

            return response()
                ->json([
                    'errors' => [
                        'file' => $beatifiedErrors,
                    ]
                ], 422);
        }
    }
}
