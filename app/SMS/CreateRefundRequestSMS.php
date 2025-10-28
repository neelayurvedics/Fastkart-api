<?php

namespace App\SMS;

use App\Helpers\Helpers;
use App\Http\Traits\MessageTrait;
use App\Notifications\CreateRefundRequestNotification;

class CreateRefundRequestSMS
{
     use MessageTrait;

     /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */

      public function send($notifiable, CreateRefundRequestNotification $cancelRefNotification)
      {
            return $cancelRefNotification->toSend($notifiable);
      }

      public function isEnabled()
      {
            $settings = Helpers::getSettings();
            return ($settings['activation']['send_sms'] && $settings['sms_methods']['config']['refund_request_sms']);
      }

      public function sendSMS($notifiable, $consumer)
      {
            if ($this->isEnabled()) {
                  $message = [
                        'to' =>'+'.$notifiable->country_code.$notifiable->phone,
                        'body' => __('sms.refund_request_admin',['consumer' => $consumer])
                  ];

                  return $this->sendMessage($message,Helpers::getDefaultSMSMethod());
            }
      }
}
