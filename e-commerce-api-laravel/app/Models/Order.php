<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
	use HasFactory;

	// mass-assignment
	protected $fillable = [
		'user_id',
		'order_number',
		'status',
		'subtotal',
		'tax',
		'shipping',
		'total',
		'shipping_name',
		'shipping_email',
		'shipping_phone',
		'shipping_address',
		'shipping_city',
		'shipping_state',
		'shipping_zip',
		'shipping_country',
		'notes',
		'completed_at'
	];

	// auto value conversion
	protected $casts = [
		'subtotal' => 'decimal:2',
		'tax' => 'decimal:2',
		'shipping' => 'decimal:2',
		'total' => 'decimal:2',
		'completed_at' => 'datetime'
	];

	// Relationships
	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function items()
	{
		return $this->hasMany(OrderItems::class);
	}

	// event lifecycle
	protected static function booted()
	{
		static::creating(function ($order) {
			if (empty($order->order_number)) {
				$order->order_number = 'ORD-' . strtoupper(uniqid());
			}
		});

		static::updating(function ($order) {
			// if status now delivered and status been changed recently and complete_at field is still empty
			if ($order->isDirty('status') && $order->status === 'delivered' && !$order->completed_at) {
				$order->completed_at = now();
			}
		});
	}
}
