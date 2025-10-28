<?php

namespace App\SMS;

use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Http\Traits\MessageTrait;
use Illuminate\Notifications\Notification;
use App\Notifications\PendingOrderReminderNotification;

class PendingOrderReminderSMS
{
      use MessageTrait;

      /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  Notification  $notification
     * @return void
     */
      public function send($notifiable, PendingOrderReminderNotification $pendingOrdRemNotification)
      {
            return $pendingOrdRemNotification->toSend($notifiable);
      }

      public function isEnabled()
      {
            $settings = Helpers::getSettings();
            return ($settings['activation']['send_sms'] && $settings['sms_methods']['config']['pending_order_sms']);
      }

      public function getMessage($roleName, $order)
      {
            switch($roleName) {
                  case RoleEnum::VENDOR:
                        return __('sms.pending_order_reminder_vendor_sms',['orderNumber' => $order->order_number]);
                  case RoleEnum::ADMIN:
                        return __('sms.pending_order_reminder_admin_sms',['orderNumber' => $order->order_number]);
            }
      }

      public function sendSMS($notifiable, $roleName, $order)
      {
            if ($this->isEnabled()) {
                  $message = [
                        'to' =>'+'.$notifiable->country_code.$notifiable->phone,
                        'body' => $this->getMessage($roleName, $order)
                  ];
                  return $this->sendMessage($message,Helpers::getDefaultSMSMethod());
            }
      }
}
