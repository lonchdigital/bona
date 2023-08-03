<?php

namespace App\Http\Requests\Admin\ProductsImport;

use App\Http\Requests\BaseRequest;
use App\Services\Product\DTO\UploadProductsImportFileDTO;

class UploadProductsImportFileRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:xlsx',
            ]
        ];
    }

    public function attributes()
    {
        return [
            'file' => mb_strtolower(trans('admin.products_import_file_for_import')),
        ];
    }

    public function toDTO(): UploadProductsImportFileDTO
    {
        return new UploadProductsImportFileDTO(
            $this->file('file'),
        );
    }
}
