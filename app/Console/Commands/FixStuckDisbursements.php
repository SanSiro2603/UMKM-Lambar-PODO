<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\XenditService;
use Illuminate\Console\Command;

class FixStuckDisbursements extends Command
{
    protected $signature = 'xendit:fix-disbursements';
    protected $description = 'Fix transactions stuck at "paid" status without disbursement';

    public function handle(XenditService $xendit): int
    {
        $stuck = Transaction::where('status', 'paid')
            ->whereNotNull('xendit_invoice_id')
            ->get();

        $count = $stuck->count();
        $this->info("Found {$count} stuck transaction(s).");

        foreach ($stuck as $trx) {
            $this->info("Processing trx#{$trx->id} order=" . ($trx->order->order_code ?? 'N/A') . " total={$trx->total_amount}");
            
            $result = $xendit->disbursementToSeller($trx);
            
            if ($result['success']) {
                $this->info("  -> Disbursed: fee={$result['platform_fee']} seller={$result['seller_amount']}");
            } else {
                $this->error("  -> Failed: {$result['message']}");
            }
        }

        $this->info('Done.');
        return 0;
    }
}