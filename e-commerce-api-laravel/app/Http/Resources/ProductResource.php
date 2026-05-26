<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
			'name' => $this->name,
			'slug' => $this->slug,
			'description' => $this->description,
			'price' => $this->price,
			'compare_price' => $this->compare_price,
			'on_sale' => $this->on_sale,
			'stock' => $this->stock,
			'sku' => $this->sku,
			'image' => $this->image,
			'image_url' => $this->image_url,
			'is_active' => $this->is_active,
			'created_at' => $this->created_at->toDateTimeString(),
		];
    }
}
