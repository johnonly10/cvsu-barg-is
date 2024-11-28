<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class StockUpdate extends Notification
{
    use Queueable;

    public $product;
    public $message;

    public function __construct($product, $message)
    {
        $this->product = $product;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; 
    }
    public function toDatabase($notifiable)
    {
        return [
            'message' => "New message from {$this->product->name}: {$this->product->message}",
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('New Stock Available: ' . $this->product->name)
                    ->line('We are excited to inform you that new stock is available for the product: ' . $this->product->name . '.')
                    ->action('View Product', url('/admin/product/edit/{id}' . $this->product->id))
                    ->line('Thank you for shopping with us!');
    }

    public function toArray($notifiable)
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'message' => $this->message,

        ];
    }
}