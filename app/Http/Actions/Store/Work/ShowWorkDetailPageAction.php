<?php

namespace App\Http\Actions\Store\Work;

use Abordage\LastModified\Facades\LastModified;
use App\Models\Work;
use App\Services\Work\WorkService;

class ShowWorkDetailPageAction
{
    public function __invoke(Work $work, WorkService $workService)
    {
        abort_unless($work->is_published, 404);

        LastModified::set($work->updated_at);

        return view('pages.works.detail', [
            'work' => $work->load('images'),
            'otherWorks' => $workService->getOtherWorks($work),
        ]);
    }
}
