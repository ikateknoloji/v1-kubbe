<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \App\Events\AdminNotificationEvent::class => [
            \App\Listeners\AdminNotificationListener::class,
        ],

        \App\Events\CustomerNotificationEvent::class => [
            \App\Listeners\CustomerNotificationListener::class,
        ],

        \App\Events\ManufacturerNotificationEvent::class => [
            \App\Listeners\ManufacturerNotificationListener::class,
        ],

        \App\Events\OrderStatusChangedEvent::class => [
            \App\Listeners\OrderStatusChangedListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
