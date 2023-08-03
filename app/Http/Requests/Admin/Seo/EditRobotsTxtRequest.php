<?php

namespace App\Http\Requests\Admin\Seo;

use App\Http\Requests\BaseRequest;
use App\Services\Application\DTO\EditRobotsTxtDto;

class EditRobotsTxtRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'content' => [
                'required',
                'string'
            ]
        ];
    }

    public function attributes(): array
    {
        return [
            'content' => mb_strtolower(trans('admin.robots_txt')),
        ];
    }

    public function toDTO(): EditRobotsTxtDto
    {
        return new EditRobotsTxtDto(
            $this->input('content')
        );
    }
}
