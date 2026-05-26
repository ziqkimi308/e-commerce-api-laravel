<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
		return [
			'id' => $this->id,
			'order_number' => $this->order_number,
			'status' => $this->status,
			'subtotal' => $this->subtotal,
			'tax' => $this->tax,
			'shipping' => $this->shipping,
			'total' => $this->total,
			'items' => OrderItemResource::collection($this->whenLoaded('items')),
			'shipping_info' => [
				'name' => $this->shipping_name,
				'email' => $this->shipping_email,
				'phone' => $this->shipping_phone,
				'address' => $this->shipping_address,
				'city' => $this->shipping_city,
				'state' => $this->shipping_state,
				'zip' => $this->shipping_zip,
				'country' => $this->shipping_country,
			],
			'notes' => $this->notes,
			'completed_at' => $this->completed_at?->toDateTimeString(),
			'created_at' => $this->created_at->toDateTimeString(),
			'updated_at' => $this->updated_at->toDateTimeString(),
		];
    }
}
