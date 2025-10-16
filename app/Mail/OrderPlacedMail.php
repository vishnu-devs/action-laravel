<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public string $subjectText;
    public ?string $recipientName;
    public ?string $note;

    public function __construct(Order $order, string $subjectText = 'Order Confirmation', ?string $recipientName = null, ?string $note = null)
    {
        $this->order = $order->load(['user', 'products.user']);
        $this->subjectText = $subjectText;
        $this->recipientName = $recipientName;
        $this->note = $note;
    }

    public function build()
    {
        return $this->subject($this->subjectText)
            ->view('emails.order-placed')
            ->with([
                'order' => $this->order,
                'recipientName' => $this->recipientName,
                'note' => $this->note,
            ]);
    }
}