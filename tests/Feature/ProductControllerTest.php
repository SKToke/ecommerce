<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function itListsAllProductOrderByName()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Product::factory(50)->create();
        $response = $this->get('/api/products?item=25');
        $response->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links'])
            ->assertJsonCount(25, 'data')
            ->assertJsonStructure(['data' => ['*' => ['id', 'name', 'description', 'price', 'quantity', 'image']]]);
    }

    /**
     * @test
     */
    public function itSearchAProductByName()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Product::factory(20)->create();
        $product = Product::factory()->create(['name' => 'Search Product']);
        $response = $this->get('/api/products?query=' . $product->name);
        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', $product->name);
    }

    /**
     * @test
     */
    public function itShortAProductByPriceASC()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Product::factory(20)->create();
        $product = Product::factory()->create(['price' => 0]);
        $response = $this->get('/api/products?sort=price&type=asc');
        $response->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('data.0.price', $product->price);
    }

    /**
     * @test
     */
    public function itShortAProductByPriceDESC()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Product::factory(20)->create();
        $product = Product::factory()->create(['price' => 99999999]);
        $response = $this->get('/api/products?sort=price&type=desc');
        $response->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('data.0.price', $product->price);
    }

    /**
     * @test
     */
    public function itCanCreateAProductIfAdmin()
    {
        $user = User::factory()->admin()->create();
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
        $this->assertDatabaseHas('products', ['id' => $response->json('data.id')]);
    }

    /**
     * @test
     */
    public function itCanNotCreateAProductIfNotAdmin()
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
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function itCanUpdateAProductIfAdmin()
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);
        $product = Product::factory()->create();
        $response = $this->putJson('/api/products/' . $product->id, ['name' => 'Updated Product Name']);
        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Product Name');;
    }

    /**
     * @test
     */
    public function itCanNotUpdatesAProductIfNotAdmin()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $product = Product::factory()->create();
        $response = $this->putJson('/api/products/' . $product->id, ['name' => 'Updated Product Name']);
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function itCanRemoveAProductIfAdmin()
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);
        $product = Product::factory()->create();
        $response = $this->deleteJson('/api/products/' . $product->id);
        $response->assertOk();
        $this->assertModelMissing($product);
    }

    /**
     * @test
     */
    public function itCanNotRemoveAProductIfNotAdmin()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $product = Product::factory()->create();
        $response = $this->deleteJson('/api/products/' . $product->id);
        $response->assertForbidden();

    }
}
