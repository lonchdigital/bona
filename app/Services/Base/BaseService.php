<?php

namespace App\Services\Base;

use Closure;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

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
}
