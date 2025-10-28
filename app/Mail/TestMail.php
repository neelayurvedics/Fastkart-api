<?php

namespace App\Mail;

use App\Helpers\Helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $settings = Helpers::getSettings();
        if ($settings['email']['system_test_mail']) {
            return $this->subject("Test Mail")
                ->from(request()->mail_from_address, request()?->mail_from_name)
                ->markdown('emails.test-mail')
                ->with(['request' => request()]);
        }
    }
}
