<?php

namespace App\Mail;

use App\Models\Product;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LowStockNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $product;

    /**
     * Create a new message instance.
     */
    public function __construct(Product $product)
    {
        $this->product = $product;

        // Trigger notification storage
        $this->storeNotification();
    }

    /**
     * Store the notification in the database.
     */
    protected function storeNotification()
    {
        // Assuming the notification is associated with the authenticated user or a specific entity
        $notifiable = Auth::user(); // or replace with any notifiable model, like an admin or manager

        // Create a new notification record in the database
        Notification::create([
            'id' => (string) Str::uuid(), // Generate a UUID
            'type' => 'low_stock',
            'notifiable_id' => $notifiable->id,
            'notifiable_type' => get_class($notifiable), // Polymorphic relationship
            'data' => json_encode([
                'message' => 'Low Stock Alert: ' . $this->product->name . ' is running low on stock.',
                'product_id' => $this->product->id,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Low Stock Alert: ' . $this->product->name)
                    ->view('emails.low_stock_notification');
    }
}
