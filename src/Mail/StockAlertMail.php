<?php

namespace Webkul\InventoryPlus\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Webkul\InventoryPlus\Models\StockAlertLog;

class StockAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public StockAlertLog $alert
    ) {}

    public function envelope(): Envelope
    {
        $productName = $this->alert->product?->name ?? "Product #{$this->alert->product_id}";

        return new Envelope(
            subject: "[Stock Alert] {$this->alert->alert_type->label()}: {$productName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'inventory-plus::admin.emails.stock-alert',
            with: [
                'alert' => $this->alert,
                'product' => $this->alert->product,
                'rule' => $this->alert->rule,
            ],
        );
    }
}
