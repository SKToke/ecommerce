<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Validators\OrderValidator;
use App\Notifications\OrderPlacedNotification;
use DB;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Notification;

class OrderController extends Controller
{
    public function index(): JsonResource
    {
        list($query, $status) = [request('query'), request('status')];
        $orders = Order::when($query, function ($builder) use ($query) {
            $builder->where('order_id', 'LIKE', '%' . $query . '%');
        })->when($status, function ($builder) use ($status) {
            $builder->where('status', $status);
        })->get();
        return OrderResource::collection($orders);
    }

    public function create(): JsonResource
    {
        abort_unless(auth()->id(), Response::HTTP_FORBIDDEN);
        $attributes = (new OrderValidator())->validate($order = new Order(), request()->all());
        $order->fill($attributes);
        abort_unless($this->_checkProductAvailability($order), Response::HTTP_UNPROCESSABLE_ENTITY, 'Out of stock');
        $order->save();
        Notification::send(User::where('is_admin', true)->get(), new OrderPlacedNotification($order));
        return OrderResource::make($order);
    }

    private function _checkProductAvailability(Order $order): bool
    {
        $product = Product::find($order->product_id);
        return $product->quantity >= $order->quantity;
    }

    public function update(Order $order): JsonResource
    {
        abort_unless(auth()->id(), Response::HTTP_FORBIDDEN);
        abort_if(!auth()->user()->is_admin && $order->status != Order::STATUS_PENDING, Response::HTTP_UNPROCESSABLE_ENTITY);
        $attributes = (new OrderValidator())->validate($order, request()->all());
        $order->fill($attributes);
        (auth()->user()->is_admin && Order::STATUS_DELIVERED == $order->status) ? $this->_updateProductQuantityAndSave($order) : $order->save();
        return OrderResource::make($order);
    }

    private function _updateProductQuantityAndSave(Order $order): void
    {
        $product = Product::find($order->product_id);
        $product->quantity = $product->quantity - $order->quantity;
        DB::transaction(function () use ($order, $product) {
            $order->save();
            $product->save();
        });
    }
}
