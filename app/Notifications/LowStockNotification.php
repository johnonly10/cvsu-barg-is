<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class LowStockNotification extends Notification
{
    use Queueable;

    public $product;
    public $quantity;

    /**
     * Create a new notification instance.
     */
    public function __construct($product, $quantity)
    {
        $this->product = $product;
        $this->quantity = $quantity;
      
    }
    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    
     public function toDatabase($notifiable): array
     {
         return [
             'product_name' => $this->product->name,
             'quantity' => $this->quantity,
             'message' => "{$this->product->name} stock is low. Only {$this->quantity} left in stock.",
         ];
     }

     public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'product_name' => $this->product->name,
            'quantity' => $this->quantity,
            'message' => "{$this->product->name} stock is low. Only {$this->quantity} left in stock.",
        ]);
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'product_name' => $this->product->name,
            'quantity' => $this->quantity,
            'message' => "{$this->product->name} stock is low. Only {$this->quantity} left in stock.",
        ];
    }
}
