<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'Order Confirmation - Festival Tickets #' . $this->order->id,
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.order-confirmation',
            with: [
                'order' => $this->order,
            ],
        );
    }

    public function attachments()
    {
        \Log::info("Starting attachment generation for order: {$this->order->id}");

        // For now, let's not attach anything to see if email works
        $attachments = [];

        // Load tickets relationship if not already loaded
        $this->order->load('tickets.festival');

        \Log::info("Order has {$this->order->tickets->count()} tickets");

        // Just return empty array for now to test if email works without PDF
        \Log::info("Returning " . count($attachments) . " attachments");

        return $attachments;
    }
}
