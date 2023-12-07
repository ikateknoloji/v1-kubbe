<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Support\Facades\Log;
use App\Events\CustomerNotificationEvent;

class CustomerNotificationListener
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
    public function handle(CustomerNotificationEvent $event)
    {
        Log::debug('Customer Notification Event Received', ['data' => $event->message]);
    }
}
