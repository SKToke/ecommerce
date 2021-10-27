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
        return validator($attributes,
            [
                'id' => [Rule::when($order->exists, 'sometimes'), Rule::unique('orders', 'id')->ignore($order)],
                'product_id' => [Rule::when($order->exists, 'sometimes'), 'required', 'numeric', Rule::exists('products', 'id')],
                'price' => [Rule::when($order->exists, 'sometimes'), 'required', 'numeric', 'digits_between:1,4'],
                'quantity' => [Rule::when($order->exists, 'sometimes'), 'required', 'numeric', 'digits_between:1,4']
            ]
        )->validate();
    }
}
