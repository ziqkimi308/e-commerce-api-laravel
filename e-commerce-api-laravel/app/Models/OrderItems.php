<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
    use HasFactory;

	// mass assignment
	protected $fillable = [
		'order_id',
		'product_id',
		'product_name',
		'product_sku',
		'price',
		'quantity',
		'total'
	];

	// auto value conversion
	protected $casts = [
		'price' => 'decimal:2',
		'total' => 'decimal:2'
	];

	// relationships
	public function order()
	{
		return $this->belongsTo(Order::class);
	}

	public function product()
	{
		return $this->belongsTo(Product::class);
	}
}
