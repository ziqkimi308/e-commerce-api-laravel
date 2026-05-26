<?php

namespace App\Listeners;

use App\Events\OrderPlace;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateOrderNotification
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
    public function handle(OrderPlace $event): void
    {
        $event->order->user->notify(new OrderPlacedNotification($event->order));
    }
}
