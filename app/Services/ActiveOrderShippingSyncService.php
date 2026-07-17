<?php

namespace App\Services;

use App\Events\OrderShippingUpdated;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActiveOrderShippingSyncService
{
    public function __construct(
        private ShippingZoneCalculator $shippingCalculator,
        private XenditService $xendit,
    ) {
    }

    /**
     * @return array{orders:array<int,array<string,mixed>>,skipped:int}
     */
    public function preview(User $customer, string $destinationDistrictCode): array
    {
        $orders = $this->eligibleOrdersQuery($customer)
            ->with(['store', 'items', 'transaction'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Order $order) use ($destinationDistrictCode) {
                $shipping = $this->shippingCalculator->calculate(
                    $order->store?->district_code,
                    $destinationDistrictCode,
                );
                $subtotal = $this->subtotal($order);
                $newTotal = $subtotal + $shipping['cost'];
                $pendingInvoice = $this->currentPendingInvoice($order);

                return [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'store_name' => $order->store?->name ?? 'Toko',
                    'old_cost' => (int) ($order->shipping_cost ?? 0),
                    'new_cost' => (int) $shipping['cost'],
                    'old_total' => (int) $order->total_price,
                    'new_total' => $newTotal,
                    'zone_label' => $shipping['label'],
                    'invoice_will_reset' => $pendingInvoice !== null && $newTotal !== (int) $order->total_price,
                ];
            })
            ->all();

        return [
            'orders' => $orders,
            'skipped' => $this->ineligibleActiveOrdersQuery($customer)->count(),
        ];
    }

    /**
     * @return array{updated:int,skipped:int,failed:int,failures:array<int,string>,invoice_resets:int}
     */
    public function sync(User $customer, string $fullAddress, string $destinationDistrictCode): array
    {
        $orderIds = $this->eligibleOrdersQuery($customer)->pluck('id');
        $result = [
            'updated' => 0,
            'skipped' => $this->ineligibleActiveOrdersQuery($customer)->count(),
            'failed' => 0,
            'failures' => [],
            'invoice_resets' => 0,
        ];

        foreach ($orderIds as $orderId) {
            $order = Order::with(['store', 'items', 'transaction'])->find($orderId);
            if (! $order || ! $this->isEligible($order)) {
                $result['skipped']++;
                continue;
            }

            $shipping = $this->shippingCalculator->calculate(
                $order->store?->district_code,
                $destinationDistrictCode,
            );
            $newTotal = $this->subtotal($order) + $shipping['cost'];
            $pendingInvoice = $this->currentPendingInvoice($order);
            $invoiceMustReset = $pendingInvoice !== null && $newTotal !== (int) $order->total_price;

            if ($invoiceMustReset) {
                $expired = $this->xendit->expireInvoice($pendingInvoice->xendit_invoice_id);
                if (! ($expired['success'] ?? false)) {
                    $result['failed']++;
                    $result['failures'][] = "Pesanan #{$order->order_code}: invoice lama belum dapat diperbarui.";
                    Log::warning('Order shipping sync skipped because invoice expiry failed', [
                        'order_id' => $order->id,
                        'transaction_id' => $pendingInvoice->id,
                        'message' => $expired['message'] ?? null,
                    ]);
                    continue;
                }
            }

            try {
                $updatedOrder = DB::transaction(function () use (
                    $customer,
                    $orderId,
                    $fullAddress,
                    $shipping,
                    $newTotal,
                    $pendingInvoice,
                    $invoiceMustReset,
                ) {
                    $lockedOrder = Order::query()
                        ->where('id', $orderId)
                        ->where('customer_id', $customer->id)
                        ->lockForUpdate()
                        ->first();

                    if (! $lockedOrder) {
                        return null;
                    }

                    if ($invoiceMustReset) {
                        $lockedTransaction = Transaction::query()
                            ->where('id', $pendingInvoice->id)
                            ->lockForUpdate()
                            ->first();

                        if (! $lockedTransaction || $lockedTransaction->status !== 'pending') {
                            return null;
                        }

                        $metadata = $lockedTransaction->metadata ?? [];
                        $metadata['expired_reason'] = 'shipping_address_changed';

                        $lockedTransaction->update([
                            'status' => 'expired',
                            'expired_at' => now(),
                            'metadata' => $metadata,
                        ]);

                        if ($lockedOrder->xendit_invoice_id === $lockedTransaction->xendit_invoice_id
                            && $lockedOrder->payment_status === 'unpaid') {
                            $lockedOrder->xendit_invoice_id = null;
                            $lockedOrder->xendit_invoice_url = null;
                        } else {
                            return null;
                        }
                    }

                    if (! $this->isEligible($lockedOrder)) {
                        return null;
                    }

                    $lockedOrder->shipping_address = $fullAddress;
                    $lockedOrder->shipping_cost = $shipping['cost'];
                    $lockedOrder->shipping_zone_label = $shipping['label'];
                    $lockedOrder->total_price = $newTotal;
                    $lockedOrder->save();

                    return $lockedOrder->fresh(['store', 'items', 'transaction']);
                });

                if (! $updatedOrder) {
                    $result['skipped']++;
                    continue;
                }

                $result['updated']++;
                if ($invoiceMustReset) {
                    $result['invoice_resets']++;
                }

                event(new OrderShippingUpdated($updatedOrder));
            } catch (\Throwable $e) {
                $result['failed']++;
                $result['failures'][] = "Pesanan #{$order->order_code}: gagal menyimpan perubahan ongkir.";
                Log::error('Order shipping sync failed', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $result;
    }

    private function eligibleOrdersQuery(User $customer)
    {
        return Order::query()
            ->where('customer_id', $customer->id)
            ->where('payment_status', 'unpaid')
            ->where(function ($query) {
                $query->where('status', 'waiting_payment')
                    ->orWhere(function ($query) {
                        $query->where('status', 'processing')
                            ->where('payment_method', 'cod');
                    });
            });
    }

    private function ineligibleActiveOrdersQuery(User $customer)
    {
        return Order::query()
            ->where('customer_id', $customer->id)
            ->whereIn('status', ['waiting_payment', 'processing', 'shipped'])
            ->where(function ($query) {
                $query->where('payment_status', '!=', 'unpaid')
                    ->orWhere('status', 'shipped')
                    ->orWhere(function ($query) {
                        $query->where('status', 'processing')
                            ->where('payment_method', '!=', 'cod');
                    });
            });
    }

    private function isEligible(Order $order): bool
    {
        return $order->payment_status === 'unpaid'
            && ($order->status === 'waiting_payment'
                || ($order->status === 'processing' && $order->payment_method === 'cod'));
    }

    private function subtotal(Order $order): int
    {
        return (int) $order->items->sum(fn ($item) => $item->qty * $item->price);
    }

    private function currentPendingInvoice(Order $order): ?Transaction
    {
        $transaction = $order->transaction;

        if (! $transaction
            || $transaction->status !== 'pending'
            || ! $transaction->xendit_invoice_id
            || $order->xendit_invoice_id !== $transaction->xendit_invoice_id) {
            return null;
        }

        return $transaction;
    }
}
