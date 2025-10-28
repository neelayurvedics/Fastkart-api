<?php

namespace App\Mail;

use App\Helpers\Helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class PlaceOrderMail extends Mailable
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
        if ($settings['email']['order_confirmation_mail']) {

            $invoiceData = [
                'order' => $this->order,
                'settings' => Helpers::getSettings(),
            ];

            $invoice = PDF::loadView('emails.invoice', $invoiceData);

            return $this->subject("Your Order #{$this->order?->order_number} Confirmation")->markdown('emails.place-order')->attachData($invoice?->output(), "invoice-{$this->order?->order_number}.pdf", [
                'mime' => 'application/pdf',
            ]);
        }
    }
}
