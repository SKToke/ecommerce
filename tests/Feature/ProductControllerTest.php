<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function itListsAllProductOrderByName()
    {
        Product::factory(30)->create();
        $response = $this->get('/api/products');
        $response->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links'])
            ->assertJsonCount(20, 'data')
            ->assertJsonStructure(['data' => ['*' => ['id', 'name', 'description', 'price', 'quantity', 'image']]]);
    }

    /**
     * @test
     */
    public function itCreateAProduct()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/products', [
            'name' => 'new product',
            'description' => 'new test product',
            'price' => 100,
            'quantity' => 500,
            'image' => 'newImage.png',
        ]);

        $response->assertCreated()
            ->assertJsonCount(6, 'data')
            ->assertJsonPath('data.name', 'new product');

        $this->assertDatabaseHas('products', [
            'id' => $response->json('data.id')
        ]);
    }
}
