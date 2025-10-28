<?php

namespace App\SMS;

use App\Helpers\Helpers;
use App\Http\Traits\MessageTrait;
use App\Notifications\SignUpBonusPointsNotification;

class SignUpBonusPointsSMS
{
      use MessageTrait;

      /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
      public function send($notifiable, SignUpBonusPointsNotification $signUpBonusNotification)
      {
            return $signUpBonusNotification->toSend($notifiable);
      }

      public function isEnabled()
      {
            $settings = Helpers::getSettings();
            return ($settings['activation']['send_sms'] && $settings['sms_methods']['config']['signup_bonus_sms']);
      }

      public function sendSMS($notifiable)
      {
            //for consumer
            $settings = Helpers::getSettings();
            if ($this->isEnabled()) {
                  $message = [
                        'to' =>'+'.$notifiable->country_code.$notifiable->phone,
                        'body' => __('sms.signup_bonus_points_consumer_sms', ['signUpPoints' => $settings['wallet_points']['signup_points']])
                  ];

                  return $this->sendMessage($message,Helpers::getDefaultSMSMethod());
            }
      }
}
