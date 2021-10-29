<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    use HasFactory;

    public static function make(Order $order)
    {
        self::create([
            'order_id' => $order->id,
            'product_id' => $order->product_id,
            'price' => $order->price,
            'quantity' => $order->quantity,
            'status' => $order->status,
            'user_id' => $order->user_id,
        ]);
    }
}
