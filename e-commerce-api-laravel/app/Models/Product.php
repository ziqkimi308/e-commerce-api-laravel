<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
	use HasFactory;

	protected $fillable = [
		'name',
		'slug',
		'description',
		'price',
		'compare_price',
		'stock',
		'sku',
		'image',
		'is_active'
	];

	protected $casts = [
		'is_active' => 'boolean',
		'price' => 'decimal:2',
		'compare_price' => 'decimal:2'
	];

	// accessors
	public function getImageUrlAttribute()
	{
		if ($this->image) {
			// asset helper gives full URL
			return asset('storage/' . $this->image);
		}

		return null;
	}

	public function getOnSaleAttribute()
	{
		// compare_price represents original price
		return $this->compare_price && $this->compare_price > $this->price;
	}

	// appends use with accessor
	protected $appends = [
		'image_url',
		'on_sale'
	];

	// Scopes
	public function scopeActive($query)
	{
		return $query->where('is_active', true);
	}

	public function scopeInStock($query)
	{
		return $query->where('stock', '>', 0);
	}


	// Relationships
	public function orderItems()
	{
		return $this->hasMany(OrderItems::class);
	}

	// Event lifecycle hook
	protected static function booted()
	{
		static::creating(function ($product) {
			if (empty($product->slug)) {
				$slug = Str::slug($product->name);
				$count = 1;

				while (Product::where('slug', $slug)->exists()) {
					$slug = Str::slug($product->name) . '-' . $count;
					$count++;
				}

				$product->slug = $slug;
			}

			if (empty($product->sku)) {
				$product->sku = 'SKU-' . strtoupper(uniqid());
			}
		});
	}
}
