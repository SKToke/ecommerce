<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Validators\ProductValidator;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductController extends Controller
{
    public function index(): JsonResource
    {
        $products = Product::orderBy('name')->paginate(20);
        return ProductResource::collection($products);
    }

    public function create(): JsonResource
    {
        $attributes = (new ProductValidator())->validate($product = new Product(), request()->all());
        $product->fill($attributes)->save();
        return ProductResource::make($product);
    }
}
