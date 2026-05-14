<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
		'image_url','on_sale'
	];
}
