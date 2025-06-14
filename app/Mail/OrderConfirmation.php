<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
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
        );
    }

    public function attachments()
    {
        $attachments = [];

        foreach ($this->order->tickets as $ticket) {
            $qrCodePath = storage_path("app/public/qr_codes/{$ticket->qr_code}.png");
            if (file_exists($qrCodePath)) {
                $attachments[] = $qrCodePath;
            }
        }

        return $attachments;
    }
}
