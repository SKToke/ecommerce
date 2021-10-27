<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $product = Product::factory()->create();
        return [
//            'id' => Order::getUniqueId(),
            'id' => substr(md5(Str::random(15) . uniqid('O')), 20),
            'product_id' => $product,
            'price' => $product->price,
            'quantity' => $this->faker->numberBetween(1, 50),
            'status' => Order::STATUS_PENDING,
            'user_id' => User::factory()
        ];
    }

    public function approved(): Factory
    {
        return $this->state([
            'status' => Order::STATUS_APPROVED,
        ]);
    }
}
