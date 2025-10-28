<?php

namespace App\Mail;

use App\Helpers\Helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateOrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct($order)
    {
        $this->order =$order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $settings = Helpers::getSettings();
        if ($settings['email']['order_status_update_mail']) {
            return $this->subject("Order ID: #{$this->order?->order_number} has been {$this->order?->order_status?->name}")->markdown('emails.update-order-status');
        }
    }
}
