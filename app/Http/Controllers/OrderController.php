<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Validators\OrderValidator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class OrderController extends Controller
{

    public function create(): JsonResource
    {
        abort_unless(auth()->id(), Response::HTTP_FORBIDDEN);
        $attributes = (new OrderValidator())->validate($order = new Order(), request()->all());
        $order->fill(array_merge($attributes, ['id' => Order::getUniqueId(), 'user_id' => auth()->id()]))->save();
        return OrderResource::make($order);
    }

    public function update(Order $order): JsonResource
    {
        abort_unless(auth()->id(), Response::HTTP_FORBIDDEN);
        $attributes = (new OrderValidator())->validate($order, request()->all());
        $order->fill($attributes)->save();
        return OrderResource::make($order);
    }
}
