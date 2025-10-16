<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public string $status;
    public ?string $recipientName;

    public function __construct(Order $order, string $status, ?string $recipientName = null)
    {
        $this->order = $order;
        $this->status = $status;
        $this->recipientName = $recipientName;
    }

    public function build()
    {
        $statusLabel = $this->humanizeStatus($this->status);
        return $this->subject("Order #{$this->order->id} status updated: {$statusLabel}")
            ->view('emails.order-status-updated')
            ->with([
                'order' => $this->order,
                'statusLabel' => $statusLabel,
                'recipientName' => $this->recipientName,
            ]);
    }

    private function humanizeStatus(string $status): string
    {
        // Map internal statuses to user-friendly labels
        return match ($status) {
            'processing' => 'Accepted',
            'shipped' => 'Dispatched',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'return_requested' => 'Return Requested',
            'returned' => 'Returned',
            'refunded' => 'Refunded',
            'refund_cancelled' => 'Refund Cancelled',
            default => ucfirst($status),
        };
    }
}