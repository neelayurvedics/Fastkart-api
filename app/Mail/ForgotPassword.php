<?php

namespace App\Mail;

use App\Helpers\Helpers;
use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class ForgotPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $settings = Helpers::getSettings();
        if ($settings['email']['password_reset_mail']) {
            return $this->subject('Forgot Password')->markdown('emails.forgot-password');
        }
    }
}
