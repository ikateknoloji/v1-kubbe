<?php

namespace App\Events;

use App\Models\AdminNotification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminNotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $message)
    {
        // Veritabanına admin bildirimini kaydet
        $adminNotification = AdminNotification::create([
            'message' => json_encode($message),
            'is_read' => false,
        ]);
    
        // AdminNotification::create() metodunun döndürdüğü nesneyi kullan
        $this->message = $adminNotification;
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('admin-notifications'),
        ];
    }

    public function broadcastAs()
    {
        return 'admin-notifications';
    }

}
