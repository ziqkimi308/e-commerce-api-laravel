<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Mail\OrderStatusUpdate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusNotification implements ShouldQueue
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
    public function handle(OrderStatusChanged $event): void
    {
		# Listener received event and send mail using mail template to shipping email.
        Mail::to($event->order->shipping_email)->send(new OrderStatusUpdate(
			$event->order,
			$event->oldStatus,
			$event->newStatus
		));
    }
}
