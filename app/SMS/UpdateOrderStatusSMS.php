<?php

namespace App\SMS;

use App\Helpers\Helpers;
use App\Http\Traits\MessageTrait;
use App\Notifications\UpdateOrderStatusNotification;

class UpdateOrderStatusSMS
{
      use MessageTrait;

      /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
      public function send($notifiable, UpdateOrderStatusNotification $updateOrdStatusNotification)
      {
            return $updateOrdStatusNotification->toSend($notifiable);
      }

      public function isEnabled()
      {
            $settings = Helpers::getSettings();
            return ($settings['activation']['send_sms'] && $settings['sms_methods']['config']['update_order_status_sms']);
      }

      public function sendSMS($notifiable, $order)
      {
            // for consumer
            if ($this->isEnabled()) {
                  $message = [
                        'to' =>'+'.$notifiable->country_code.$notifiable->phone,
                        'body' => __('sms.update_order_status_consumer_sms',['orderNumber' => $order->order_number, 'OrdersName' => $order->order_status->name])
                  ];
                  return $this->sendMessage($message,Helpers::getDefaultSMSMethod());
            }
      }
}
