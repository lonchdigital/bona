<?php

namespace App\Services\Payment;

use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\PartialPaymentStatusDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\Models\Order;
use App\Services\Order\OrderService;
use App\Services\Payment\DTO\PaymentGatewayResult;
use App\Services\Telegram\TelegramNotificationService;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentReconciliationService
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly PaymentMonoBankService $monoBankService,
        private readonly OrderService $orderService,
        private readonly TelegramNotificationService $telegram,
    ) {}

    /**
     * Reconcile only existing, non-final orders. No payment or application is
     * created by this process; every external call is a read-only status call.
     *
     * @return array{checked: int, updated: int, failed: int}
     */
    public function reconcile(): array
    {
        $report = ['checked' => 0, 'updated' => 0, 'failed' => 0];
        $cutoff = now()->subHours(max(1, (int) config('payment.reconciliation.lookback_hours', 72)));
        $limit = max(1, min(200, (int) config('payment.reconciliation.batch_size', 50)));

        $orders = Order::query()
            ->where('created_at', '>=', $cutoff)
            ->whereIn('payment_type_id', [
                PaymentTypesDataClass::CARD_PAYMENT,
                PaymentTypesDataClass::CARD_PAYMENT_PAYPART,
                PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK,
            ])
            ->whereIn('payment_status_id', [
                OrderPaymentStatusesDataClass::STATUS_UNPAID,
                OrderPaymentStatusesDataClass::STATUS_IN_PROGRESS,
                OrderPaymentStatusesDataClass::STATUS_PAYPART,
            ])
            ->oldest('id')
            ->limit($limit)
            ->get();

        foreach ($orders as $order) {
            $report['checked']++;

            try {
                $outcome = match ((int) $order->payment_type_id) {
                    PaymentTypesDataClass::CARD_PAYMENT => $this->reconcileLiqPay($order),
                    PaymentTypesDataClass::CARD_PAYMENT_PAYPART => $this->reconcilePrivatBank($order),
                    PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK => $this->reconcileMonoBank($order),
                    default => false,
                };

                if ($outcome === true) {
                    $report['updated']++;
                } elseif ($outcome === null) {
                    $report['failed']++;
                }
            } catch (Throwable $exception) {
                $report['failed']++;
                Log::error('Payment reconciliation failed for an order.', [
                    'order_id' => $order->id,
                    'payment_type_id' => $order->payment_type_id,
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return $report;
    }

    private function reconcileLiqPay(Order $order): ?bool
    {
        $result = $this->paymentService->getLiqPayPaymentStatus($order->id);
        if (! $result->successful) {
            $this->gatewayUnavailable($order, 'LiqPay', $result);

            return null;
        }

        $status = (string) ($result->data['status'] ?? '');
        if ($status === 'success' || ($status === 'sandbox' && ! app()->environment('production'))) {
            if (! $this->liqPayResponseMatchesOrder($order, $result->data)) {
                $this->gatewayUnavailable(
                    $order,
                    'LiqPay',
                    PaymentGatewayResult::failure(
                        'LiqPay status response does not match the requested order amount, currency or merchant.',
                        $result->statusCode,
                    ),
                );

                return null;
            }

            $this->orderService->updateOrderPaymentStatusId(
                $order,
                OrderPaymentStatusesDataClass::STATUS_PAID,
                'LiqPay: '.$status,
                isset($result->data['payment_id']) ? (string) $result->data['payment_id'] : null,
            );

            return true;
        }

        $errorCode = (string) ($result->data['err_code'] ?? '');

        // A customer can create a local order and then close the tab before
        // LiqPay receives it. The status API calls that payment_not_found; it
        // is an abandoned attempt, not proof that the bank declined a charge.
        if ($status === 'error' && $errorCode === 'payment_not_found') {
            return false;
        }

        if (in_array($status, ['failure', 'error', 'reversed'], true)) {
            $details = collect([
                $result->data['err_code'] ?? null,
                $result->data['err_description'] ?? null,
                $status,
            ])->filter()->implode(': ');

            $this->orderService->updateOrderPaymentStatusIdWithoutEmail(
                $order,
                OrderPaymentStatusesDataClass::STATUS_DECLINED,
                $details ?: 'LiqPay відхилив платіж.',
                isset($result->data['payment_id']) ? (string) $result->data['payment_id'] : null,
            );

            return true;
        }

        return false;
    }

    private function liqPayResponseMatchesOrder(Order $order, array $payload): bool
    {
        $expectedAmountInCents = (int) round($this->orderService->getOrderSummary($order)['total'] * 100);
        $receivedAmountInCents = is_numeric($payload['amount'] ?? null)
            ? (int) round((float) $payload['amount'] * 100)
            : -1;

        if (
            ! hash_equals((string) $order->id, (string) ($payload['order_id'] ?? ''))
            || ($payload['currency'] ?? null) !== 'UAH'
            || $receivedAmountInCents !== $expectedAmountInCents
        ) {
            return false;
        }

        return ! isset($payload['public_key'])
            || hash_equals((string) config('liqpay.public_key'), (string) $payload['public_key']);
    }

    private function reconcilePrivatBank(Order $order): ?bool
    {
        $result = $this->paymentService->getPrivateBankPartialPaymentState($order->id);
        if (! $result->successful) {
            $this->gatewayUnavailable($order, 'PrivatBank', $result);

            return null;
        }

        $paymentState = (string) ($result->data['paymentState'] ?? '');
        if (in_array($paymentState, [PartialPaymentStatusDataClass::SUCCESS, PartialPaymentStatusDataClass::LOCKED], true)) {
            $this->orderService->updateOrderPaymentStatusId(
                $order,
                OrderPaymentStatusesDataClass::STATUS_PAID,
                'PrivatBank: '.$paymentState,
                $result->traceId,
            );

            return true;
        }

        if (in_array($paymentState, [PartialPaymentStatusDataClass::CANCELED, PartialPaymentStatusDataClass::FAIL], true)) {
            $this->orderService->updateOrderPaymentStatusIdWithoutEmail(
                $order,
                OrderPaymentStatusesDataClass::STATUS_DECLINED,
                'PrivatBank: '.$paymentState,
                $result->traceId,
            );

            return true;
        }

        return false;
    }

    private function reconcileMonoBank(Order $order): ?bool
    {
        if (blank($order->mono_order_id)) {
            $this->telegram->notifyIntegrationIssueOnce(
                'monobank|missing-order-id',
                'monobank',
                'Звірка статусу замовлення #'.$order->id,
                'У локального замовлення немає ідентифікатора заявки банку.',
            );

            return null;
        }

        $result = $this->monoBankService->getOrderState((string) $order->mono_order_id);
        if (! $result->successful) {
            $this->gatewayUnavailable($order, 'monobank', $result);

            return null;
        }

        $state = (string) ($result->data['state'] ?? '');
        $subState = (string) ($result->data['order_sub_state'] ?? '');

        if (
            ($state === 'IN_PROCESS' && $subState === 'WAITING_FOR_STORE_CONFIRM')
            || ($state === 'SUCCESS' && in_array($subState, ['ACTIVE', 'DONE'], true))
        ) {
            $this->orderService->updateOrderPaymentStatusId(
                $order,
                OrderPaymentStatusesDataClass::STATUS_PAID,
                'monobank: '.trim($state.' '.$subState),
                $result->traceId,
            );

            return true;
        }

        if ($state === 'FAIL') {
            $status = match ($subState) {
                'REJECTED_BY_CLIENT' => OrderPaymentStatusesDataClass::REJECTED_BY_CLIENT,
                'CLIENT_PUSH_TIMEOUT' => OrderPaymentStatusesDataClass::CLIENT_PUSH_TIMEOUT,
                default => OrderPaymentStatusesDataClass::STATUS_DECLINED,
            };

            $this->orderService->updateOrderPaymentStatusIdWithoutEmail(
                $order,
                $status,
                'monobank: '.($subState ?: $state),
                $result->traceId,
            );

            return true;
        }

        return false;
    }

    private function gatewayUnavailable(Order $order, string $provider, PaymentGatewayResult $result): void
    {
        $this->telegram->notifyIntegrationIssueOnce(
            $provider.'|'.$result->statusCode.'|'.$result->message,
            $provider,
            'Звірка статусу замовлення #'.$order->id,
            $result->message ?: ('HTTP '.($result->statusCode ?? 'невідомо')),
            $result->traceId,
        );
    }
}
