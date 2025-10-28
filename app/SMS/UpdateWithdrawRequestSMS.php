<?php

namespace App\SMS;

use App\Helpers\Helpers;
use App\Http\Traits\MessageTrait;
use App\Notifications\UpdateWithdrawRequestNotification;

class UpdateWithdrawRequestSMS
{
   use MessageTrait;

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
      public function send($notifiable, UpdateWithdrawRequestNotification $updateWithReqNotification)
      {
            return $updateWithReqNotification->toSend($notifiable);
      }

      public function isEnabled()
      {
            $settings = Helpers::getSettings();
            return ($settings['activation']['send_sms'] && $settings['sms_methods']['config']['update_withdraw_request_sms']);
      }

      public function sendSMS($notifiable, $withdrawRequest)
      {
            // for vendor
            $symbol = Helpers::getDefaultCurrencySymbol();
            if ($this->isEnabled()) {
                  $message = [
                        'to' =>'+'.$notifiable->country_code.$notifiable->phone,
                        'body' => __('sms.updated_withdraw_request_vendor_sms',['symbol' => $symbol, 'withdrawRequest' => $withdrawRequest->amount, 'withdrawRequestStatus' => $withdrawRequest->status])
                  ];
                  return $this->sendMessage($message,Helpers::getDefaultSMSMethod());
            }
      }
}
