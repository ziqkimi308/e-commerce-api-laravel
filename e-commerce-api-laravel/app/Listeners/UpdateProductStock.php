<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateProductStock implements ShouldQueue
{
	/**
	 * Create the event listener.
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Handle the event.
	 */
	public function handle(OrderPlaced $event): void
	{
		# items is relationship defined in order table which gives collection of OrderItem
		foreach ($event->order->items as $item) {
			$product = $item->product;
			if ($product) {
				# decrement is eloquent's method
				$product->decrement('stock', $item->quantity);
			}
		}
	}
}
