<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function itCanNotCreateAnOrderIfUserNotLogIn()
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
    public function itCanCreateAnOrderIfUserLoggedIn()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $product = Product::factory()->create();
        $response = $this->postJson('/api/orders', [
            'order_id' => uniqid('O'),
            'product_id' => $product->id,
            'price' => $product->price,
            'quantity' => 5,
            'status' => Order::STATUS_PENDING,
            'user_id' => $user->id,
        ]);
        $response->assertCreated()
            ->assertJsonCount(7, 'data')
            ->assertJsonPath('data.product_id', $product->id);
    }

    /**
     * @test
     */
    public function itCanUpdateAnOrderWhileStatusPending()
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create(['quantity' => 10]);
        $this->actingAs($user);
        $response = $this->putJson('/api/orders/' . $order->id, ['quantity' => 5]);
        $response->assertOk()
            ->assertJsonPath('data.quantity', 5)
            ->assertJsonPath('data.id', $order->id);
    }

    /**
     * @test
     */
    public function itCanNotUpdateAnOrderIfBuyerWhileStatusNotPending()
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create(['quantity' => 20, 'status' => Order::STATUS_APPROVED]);
        $this->actingAs($user);
        $response = $this->putJson('/api/orders/' . $order->id, ['quantity' => 10]);
        $response->assertUnprocessable();
    }

    /**
     * @test
     */
    public function itCanUpdateAnOrderIfAdminWhileStatusPending()
    {
        $user = User::factory()->admin()->create();
        $order = Order::factory()->for($user)->create(['quantity' => 20, 'status' => Order::STATUS_APPROVED]);
        $this->actingAs($user);
        $response = $this->putJson('/api/orders/' . $order->id, ['quantity' => 10]);
        $response->assertOk()
            ->assertJsonPath('data.quantity', 10)
            ->assertJsonPath('data.id', $order->id);
    }

    /**
     * @test
     */
    public function itCanApproveAnOrderIfUserIsAdmin()
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);
        $order = Order::factory()->for($user)->create();
        $response = $this->putJson('/api/orders/' . $order->id, ['status' => Order::STATUS_APPROVED]);
        $response->assertOk()
            ->assertJsonPath('data.status', Order::STATUS_APPROVED)
            ->assertJsonPath('data.id', $order->id);
    }

    /**
     * @test
     */
    public function itCanRejectAnOrderIfUserIsAdmin()
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);
        $order = Order::factory()->for($user)->create();
        $response = $this->putJson('/api/orders/' . $order->id, ['status' => Order::STATUS_REJECTED]);
        $response->assertOk()
            ->assertJsonPath('data.status', Order::STATUS_REJECTED)
            ->assertJsonPath('data.id', $order->id);
    }

    /**
     * @test
     */
    public function itCanSearchAnOrderByOrderUniqueId()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $uniqueId = uniqid('O');
        $order = Order::factory()->for($user)->create(['order_id' => $uniqueId]);
        $response = $this->get('/api/orders?query=' . $uniqueId);
        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.order_id', $order->order_id);
    }

    /**
     * @test
     */
    public function itCanFilterOrdersByStatus()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        //APPROVED
        Order::factory(10)->for($user)->create(['status' => Order::STATUS_APPROVED]);
        $response = $this->get('/api/orders?status=' . Order::STATUS_APPROVED);
        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('data.0.status', Order::STATUS_APPROVED);
        //SHIPPED
        Order::factory(50)->for($user)->create(['status' => Order::STATUS_SHIPPED]);
        $response = $this->get('/api/orders?status=' . Order::STATUS_SHIPPED);
        $response->assertOk()
            ->assertJsonCount(50, 'data')
            ->assertJsonPath('data.0.status', Order::STATUS_SHIPPED);
    }


    /**
     * @test
     */
    public function itCreateAnOrderAlsoSendNotificationToAdmins()
    {
        Notification::fake();
        $admin = User::factory()->create(['is_admin' => true]);

        $user = User::factory()->create();
        $this->actingAs($user);
        $product = Product::factory()->create();
        $response = $this->postJson('/api/orders', [
            'order_id' => uniqid('O'),
            'product_id' => $product->id,
            'price' => $product->price,
            'quantity' => 5,
            'status' => Order::STATUS_PENDING,
            'user_id' => $user->id,
        ]);
        $response->assertCreated()
            ->assertJsonCount(7, 'data')
            ->assertJsonPath('data.product_id', $product->id);

        Notification::assertSentTo($admin, OrderPlacedNotification::class);
    }


    /**
     * @test
     */
    public function itUpdateProductQuantityIfAdminMarkAnOrderDelivered()
    {
        $product = Product::factory()->create(['quantity' => 100]);

        $user = User::factory()->create();
        $this->actingAs($user);
        $order = Order::factory()->for($user)->create([
            'order_id' => uniqid('O'),
            'product_id' => $product->id,
            'price' => $product->price,
            'quantity' => 10,

        ]);
        $user = User::factory()->admin()->create();
        $this->actingAs($user);
        $response = $this->putJson('/api/orders/' . $order->id, ['status' => Order::STATUS_DELIVERED]);
        $response->assertOk()
            ->assertJsonPath('data.status', Order::STATUS_DELIVERED)
            ->assertJsonPath('data.id', $order->id);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'quantity' => $product->quantity - $order->quantity]);
    }


    /**
     * @test
     */
    public function itShowsOutOfStockMessageWhileCreatingAnOrderIfTheProductQuantityIsZero()
    {
        $product = Product::factory()->create(['quantity' => 0]);
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->postJson('/api/orders', [
            'order_id' => uniqid('O'),
            'product_id' => $product->id,
            'price' => $product->price,
            'quantity' => 5,
            'status' => Order::STATUS_PENDING,
            'user_id' => $user->id,
        ]);
        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Out of stock');
    }
}
