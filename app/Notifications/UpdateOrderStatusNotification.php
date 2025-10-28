<?php

namespace App\Notifications;

use App\Models\Order;
use App\Helpers\Helpers;
use Illuminate\Bus\Queueable;
use App\SMS\UpdateOrderStatusSMS;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class UpdateOrderStatusNotification extends Notification
{
    use Queueable;

    private $order;
    private $consumer;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, $consumer)
    {
        $this->order = $order;
        $this->consumer = $consumer;
    }

   /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [UpdateOrderStatusSMS::class,'database','mail'];
    }

    public function toSend(object $notifiable)
    {
        return (new UpdateOrderStatusSMS)->sendSMS($notifiable, $this->order);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        $settings = Helpers::getSettings();
        if($settings['email']['order_status_update_mail']) {
            if ($this->order->consumer_id) {
                $consumer = Helpers::getConsumerById($this->order?->consumer_id);
                if ($consumer) {
                    return (new MailMessage)
                        ->subject("Order ID: #{$this->order?->order_number} has been {$this->order?->order_status?->name}")
                        ->greeting("Hello {$consumer->name},")
                        ->line("We wanted to provide you with an update regarding your recent order, ID. #{$this->order?->order_number}.")
                        ->line("Your order status has been updated to {$this->order?->order_status?->name}. ")
                        ->line('Please feel free to reach out to us if you have any questions or need assistance.')
                        ->line('Thank you for choosing us for your shopping experience. We value your trust and support!');
                }
            }
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // for consumer
        return [
            'title' => __('notifications.update_order_status_title'),
            'message' => __('notifications.update_order_status_consumer',['orderNumber' => $this->order->order_number, 'OrdersName' => $this->order->order_status->name]),
            'type' => "order",
            'order_number' => $this->order?->order_number
        ];
    }
}
