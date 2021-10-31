<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Validators\ProductValidator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function index(): JsonResource
    {
        abort_unless(auth()->user()->tokenCan('product.view'), Response::HTTP_FORBIDDEN);
        list($query, $sort, $type, $item) = [request('query'), request('sort') ?: 'name', request('type') ?: 'asc', request('item') ?: 20];
        $products = Product::when($query, function ($builder) use ($query) {
            $builder->where('name', 'LIKE', '%' . $query . '%');
        })->orderBy($sort, $type)->paginate($item);;
        return ProductResource::collection($products);
    }

    public function create(): JsonResource
    {
        abort_unless(auth()->user()->is_admin, Response::HTTP_FORBIDDEN);
        $attributes = (new ProductValidator())->validate($product = new Product(), request()->all());
        $product->fill($attributes)->save();
        return ProductResource::make($product);
    }

    public function update(Product $product): JsonResource
    {
        abort_unless(auth()->user()->is_admin, Response::HTTP_FORBIDDEN);
        $attributes = (new ProductValidator())->validate($product, request()->all());
        $product->fill($attributes)->save();
        return ProductResource::make($product);
    }

    public function delete(Product $product)
    {
        abort_unless(auth()->user()->is_admin, Response::HTTP_FORBIDDEN);
        $product->delete();
    }
}
