<?php

namespace App\Models\Validators;

use App\Models\Order;
use Illuminate\Validation\Rule;

class OrderValidator
{
    /**
     * @param Order $order
     * @param array $attributes
     * @return array
     */
    public function validate(Order $order, array $attributes): array
    {
        return validator($attributes, [
            'product_id' => [Rule::when($order->exists, 'sometimes'), 'required', 'numeric', Rule::exists('products', 'id')],
            'price' => [Rule::when($order->exists, 'sometimes'), 'required', 'numeric', 'digits_between:1,4'],
            'quantity' => [Rule::when($order->exists, 'sometimes'), 'required', 'numeric', 'digits_between:1,4'],
            'status' => [Rule::when(!auth()->user()->is_admin, 'sometimes'), 'required', Rule::in(Order::STATUS_APPROVED, Order::STATUS_PENDING, Order::STATUS_REJECTED, Order::STATUS_DELIVERED, Order::STATUS_PROCESSING, Order::STATUS_SHIPPED)]
        ])->validate();
    }
}
