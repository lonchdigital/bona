<?php

namespace App\Http\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthCheckAction
{
    public function __invoke(): JsonResponse
    {
        DB::select('select 1');

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
