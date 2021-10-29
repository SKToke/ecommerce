<?php

namespace App\Jobs;

use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeliveredOrdersTransferJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $deliveredOrders = Order::where('status', Order::STATUS_DELIVERED)->get();
        if ($deliveredOrders) {
            $data = $deliveredOrders->map(function ($item) {
                return $item->only('order_id', 'product_id', 'price', 'quantity', 'user_id');
            })->toArray();
            Delivery::insert($data);
            Order::destroy($deliveredOrders->pluck('id'));
        }
    }
}
