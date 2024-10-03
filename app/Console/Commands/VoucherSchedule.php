<?php

namespace App\Console\Commands;

use App\Models\Voucher;
use Illuminate\Console\Command;

class VoucherSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voucherSchedule:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'activate the voucher';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $voucher = Voucher::where('status', '0')
            ->update([
                'status' => '1',
                // 'expired' => now()->addDay(7)
            ]);

        $this->info("Activated voucher {$voucher}");
    }
}
