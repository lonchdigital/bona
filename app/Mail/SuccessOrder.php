<?php

namespace App\Mail;

use App\Support\Commerce\ProductBundle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SuccessOrder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(private $order) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans('base.order_product'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $this->order->loadMissing([
            'products.colors',
            'products.productType.attributes',
        ]);

        return new Content(
            view: 'emails.success-order',
            with: [
                'order' => $this->order,
                'orderProductGroups' => ProductBundle::group($this->order->products),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
