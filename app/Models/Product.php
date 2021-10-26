<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $casts =[
        'price'=>'integer',
        'created_at'=>'immutable_datetime',
        'updated_at'=>'immutable_datetime'
    ];
}
