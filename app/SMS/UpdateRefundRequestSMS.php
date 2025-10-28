<?php

namespace App\SMS;

use App\Helpers\Helpers;
use App\Http\Traits\MessageTrait;
use App\Notifications\UpdateRefundRequestNotification;

class UpdateRefundRequestSMS
{
      use MessageTrait;

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
      public function send($notifiable, UpdateRefundRequestNotification $updateRefReqNotification)
      {
            return $updateRefReqNotification->toSend($notifiable);
      }

      public function isEnabled()
      {
            $settings = Helpers::getSettings();
            return ($settings['activation']['send_sms'] && $settings['sms_methods']['config']['update_refund_request_sms']);
      }

      public function sendSMS($notifiable, $refund)
      {
            //for consumer
            if ($this->isEnabled()) {
                  $message = [
                        'to' =>'+'.$notifiable->country_code.$notifiable->phone,
                        'body' => __('sms.update_refund_request_consumer_sms',['refundStatus' => $refund->status])
                  ];
                  return $this->sendMessage($message,Helpers::getDefaultSMSMethod());
            }
      }
}
