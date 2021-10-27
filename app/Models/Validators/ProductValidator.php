<?php

namespace App\Models\Validators;

use App\Models\Product;
use Illuminate\Validation\Rule;

class ProductValidator
{
    /**
     * @param Product $product
     * @param array $attributes
     * @return array
     */
    public function validate(Product $product, array $attributes): array
    {
        return validator($attributes,
            [
                'name' => [Rule::when($product->exists, 'sometimes'), 'required', 'string', 'max:190'],
                'description' => [Rule::when($product->exists, 'sometimes'), 'required', 'string', 'max:999'],
                'price' => [Rule::when($product->exists, 'sometimes'), 'required', 'numeric', 'digits_between:1,4'],
                'quantity' => [Rule::when($product->exists, 'sometimes'), 'required', 'numeric', 'digits_between:1,4'],
                'image' => [Rule::when($product->exists, 'sometimes'), 'required', 'string']
            ]
        )->validate();
    }
}
