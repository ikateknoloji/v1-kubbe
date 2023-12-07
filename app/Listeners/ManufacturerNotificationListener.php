<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use App\Events\ManufacturerNotificationEvent;

class ManufacturerNotificationListener
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
    public function handle(ManufacturerNotificationEvent $event)
    {
        Log::debug('Manufacturer Notification Event Received', ['data' => $event->message]);
    }
}
