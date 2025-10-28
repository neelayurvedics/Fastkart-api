<?php

namespace App\Notifications;

use App\Models\Order;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use Illuminate\Bus\Queueable;
use App\SMS\PendingOrderReminderSMS;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PendingOrderReminderNotification extends Notification
{
    use Queueable;

    private $order;
    private $roleName;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, $roleName)
    {
        $this->order = $order;
        $this->roleName = $roleName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', PendingOrderReminderSMS::class];
    }

    public function toSend(object $notifiable)
    {
        switch ($this->roleName) {
            case RoleEnum::VENDOR:
                return self::toVendorSMS($notifiable);
            case RoleEnum::ADMIN:
                return self::toAdminSMS($notifiable);
        }
    }

    public function toAdminSMS($notifiable)
    {
        return (new PendingOrderReminderSMS)->sendSMS($notifiable,  $this->roleName, $this->order);
    }

    public function toVendorSMS($notifiable)
    {
        return (new PendingOrderReminderSMS)->sendSMS($notifiable,  $this->roleName, $this->order);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        $settings = Helpers::getSettings();
        if($settings['email']['pending_order_alert_mail']) {
            switch ($this->roleName) {
                case RoleEnum::VENDOR:
                    return $this->toVendorMail();
                case RoleEnum::ADMIN:
                    return $this->toAdminMail();
            }
        }
    }

    public function toAdminMail(): MailMessage
    {
        return (new MailMessage)
            ->subject("Attention Needed: Order #{$this->order->order_number}")
            ->line('An order has been pending for more than 24 hours and requires your attention.')
            ->line('Order Payment Status: ' . $this->order->payment_status)
            ->line('Current Order Status: ' . $this->order->order_status->name)
            ->line('Please review the order status and take necessary action.');
    }

    public function toVendorMail(): MailMessage
    {
        return (new MailMessage)
            ->subject("Action Required: Order #{$this->order->order_number}")
            ->line('An order has been pending for more than 24 hours and requires your attention.')
            ->line('Order Payment Status: ' . $this->order->payment_status)
            ->line('Current Order Status: ' . $this->order->order_status->name)
            ->line('Please update the order status as soon as possible.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        switch ($this->roleName) {
            case RoleEnum::VENDOR:
                $message = __('notifications.pending_order_reminder_vendor',['orderNumber' => $this->order->order_number]);
                $title = __('notifications.pending_order_reminder_vendor_title');
                break;
            case RoleEnum::ADMIN:
                $message = __('notifications.pending_order_reminder_admin',['orderNumber' => $this->order->order_number]);
                $title = __('notifications.pending_order_reminder_admin_title');
                break;
        }

        return [
            'title' => $title,
            'message' => $message,
            'type' => "order",
            'order_number' => $this->order?->order_number
        ];
    }
}
