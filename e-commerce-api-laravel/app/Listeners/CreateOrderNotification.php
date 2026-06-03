<?php

namespace App\Listeners;

use App\Events\OrderPlace;
use App\Events\OrderPlaced;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateOrderNotification implements ShouldQueue
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
        $event->order->user->notify(new OrderPlacedNotification($event->order));
    }
}
