<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VendorOrderNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public User $vendor;

    public function __construct(Order $order, User $vendor)
    {
        $this->order = $order->load(['user', 'products.user']);
        $this->vendor = $vendor;
    }

    public function build()
    {
        return $this->subject('New Order for Your Products')
            ->view('emails.vendor-order');
    }
}