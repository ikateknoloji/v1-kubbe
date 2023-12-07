<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use App\Events\OrderStatusChangedEvent;

class OrderStatusChangedListener
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
    public function handle(OrderStatusChangedEvent $event)
    {
        Log::debug('Order Status Changed Event Received', ['data' => $event->message]);
    }
}
