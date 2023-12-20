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
use App\Models\UserNotification;
use App\Models\Order;

class OrderStatusChangedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $message;
    
    /**
     * Create a new event instance.
     */
    public function __construct(Order $order, array $message)
    {
        $this->order = $order;
    
        // Müşteri için bildirim oluşturun ve kaydedin
        $customerNotification = UserNotification::create([
            'user_id' => $order->customer_id,
            'message' => json_encode($message),
        ]);
    
        // Üretici için bildirim oluşturun ve kaydedin (eğer varsa)
        $manufacturerNotification = null;
        if (!is_null($order->manufacturer_id)) {
            $manufacturerNotification = UserNotification::create([
                'user_id' => $order->manufacturer_id,
                'message' => json_encode($message),
            ]);
        }
    
        // Yönetici için bildirim oluşturun ve kaydedin
        $adminNotification = AdminNotification::create([
            'message' => json_encode($message),
            'is_read' => false,
        ]);
    
        // Her kullanıcı tipi için ayrı bir mesaj atayın
        $this->message = [
            'customer' => $customerNotification,
            'manufacturer' => $manufacturerNotification,
            'admin' => $adminNotification,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        // Müşteri (customer) ve admin kanallarına her durumda bildirim ekle
        $channels[] = new PrivateChannel('user.' . $this->order->customer_id);
        $channels[] = new Channel('admin-notifications');

        // manufacturer_id null değilse üreticiye özel kanala bildirim ekle
        if (!is_null($this->order->manufacturer_id)) {
            $channels[] = new PrivateChannel('user.' . $this->order->manufacturer_id);
        }

        return $channels;
    }

    public function broadcastAs()
    {
        return 'status-notifications';
    }
}
