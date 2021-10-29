<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;
    const STATUS_PROCESSING = 3;
    const STATUS_SHIPPED = 4;
    const STATUS_DELIVERED = 5;

    protected $casts = [
        'price' => 'integer',
        'quantity' => 'integer',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime'
    ];

    public static function getUniqueId()
    {
        $uniqueId = substr(md5(Str::random(15) . uniqid()), 20);
        $exists = static::where("id", "LIKE", $uniqueId . "%")->exists();
        if ($exists) {
            self::getUniqueId();
        }
        return $uniqueId;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderHistory::class);
    }
}
