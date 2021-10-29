<?php

namespace App\Http\Resources;

use App\Models\OrderHistory;
use Arr;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'histories' => OrderHistoryResource::collection($this->whenLoaded('histories')),
            $this->merge(Arr::except(parent::toArray($request), [
                'created_at', 'updated_at', 'deleted_at'
            ]))
        ];
    }
}
