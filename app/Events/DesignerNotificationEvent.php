<?php

namespace App\Events;

use App\Models\DesignerNotification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DesignerNotificationEvent implements ShouldBroadcast
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
        // Veritabanına designer bildirimini kaydet
        $designerNotification = DesignerNotification::create([
            'message' => json_encode($message),
            'is_read' => false,
        ]);
    
        // DesignerNotification::create() metodunun döndürdüğü nesneyi kullan
        $this->message = $designerNotification;
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('designer-notifications'),
        ];
    }

    public function broadcastAs()
    {
        return 'designer-notifications';
    }

}
