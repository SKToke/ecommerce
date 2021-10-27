<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function itCanNotCreateAnOrderWithoutLogIn()
    {
        $product = Product::factory()->create();
        $response = $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'price' => $product->price,
            'quantity' => 5
        ]);
        $response->assertUnauthorized();
    }

    /**
     * @test
     */
    public function itCanCreateAnOrderIfLoggedIn()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $product = Product::factory()->create();
        $response = $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'price' => $product->price,
            'quantity' => 5,
            'user_id' => $user->id,
        ]);
        $response->assertCreated()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('data.product_id', $product->id);
    }

    /**
     * @test
     */
    /*public function itCanUpdatesAnOrderWhilePending()
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create(['quantity' => 10]);
        $this->actingAs($user);
        $response = $this->putJson('/api/orders/' . $order->id, ['quantity' => 5]);
        $response->assertOk()
            ->assertJsonCount(5, 'data.quantity')
            ->assertJsonPath('data.id', $order->id);
    }*/
}
