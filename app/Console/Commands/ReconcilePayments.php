<?php

namespace App\Console\Commands;

use App\Services\Payment\PaymentReconciliationService;
use Illuminate\Console\Command;

class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile';

    protected $description = 'Read and reconcile the status of recent pending provider payments';

    public function handle(PaymentReconciliationService $payments): int
    {
        $report = $payments->reconcile();

        $this->info(sprintf(
            'Payment reconciliation complete: %d checked, %d updated, %d failed.',
            $report['checked'],
            $report['updated'],
            $report['failed'],
        ));

        return $report['failed'] === 0 ? self::SUCCESS : self::FAILURE;
    }
}
