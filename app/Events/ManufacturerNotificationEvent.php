<?php

namespace App\Events;

use App\Models\UserNotification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ManufacturerNotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $manufacturer_id;
    public $message;
    
    /**
     * Create a new event instance.
     */
    public function __construct($manufacturer_id, array $message)
    {
        $this->manufacturer_id = $manufacturer_id;
        $this->message = $message;

        // Veritabanına üretici bildirimini kaydet
        UserNotification::create([
            'user_id' => $this->manufacturer_id,
            'message' => json_encode($this->message),
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->manufacturer_id),
        ];
    }

    public function broadcastAs()
    {
        return 'manufacturer.notification';
    }
}
