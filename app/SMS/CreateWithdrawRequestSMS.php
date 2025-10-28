<?php

namespace App\SMS;

use App\Models\User;
use App\Helpers\Helpers;
use App\Http\Traits\MessageTrait;
use App\Notifications\CreateWithdrawRequestNotification;

class CreateWithdrawRequestSMS
{
      use MessageTrait;

      /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
      public function send($notifiable, CreateWithdrawRequestNotification $createWithNotification)
      {
            return $createWithNotification->toSend($notifiable);
      }

      public function isEnabled()
      {
            $settings = Helpers::getSettings();
            return ($settings['activation']['send_sms'] && $settings['sms_methods']['config']['withdraw_request_sms']);
      }

      public function sendSMS($notifiable, $withdrawRequest)
      {
            $symbol = Helpers::getDefaultCurrencySymbol();
            $vendor = User::where('id', $withdrawRequest->vendor_id)->pluck('name')->first();
            if ($this->isEnabled()) {
                  $message = [
                        'to' =>'+'.$notifiable->country_code.$notifiable->phone,
                        'body' =>  __('notifications.withdraw_request_admin',['symbol' => $symbol,'withdrawRequest' => $withdrawRequest->amount, 'vendor' => $vendor])
                  ];

                  return $this->sendMessage($message,Helpers::getDefaultSMSMethod());
            }
      }
}
