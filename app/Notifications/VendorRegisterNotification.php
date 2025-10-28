<?php

namespace App\Notifications;

use App\Models\User;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use Illuminate\Bus\Queueable;
use App\SMS\VendorRegisterSMS;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VendorRegisterNotification extends Notification
{
    use Queueable;

    private $store;

    /**
     * Create a new notification instance.
     */
    public function __construct($store)
    {
        $this->store = $store;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [VendorRegisterSMS::class,'database','mail'];
    }

    public function toSend(object $notifiable)
    {
        return (new VendorRegisterSMS)->sendSMS($notifiable, $this->store);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        $settings = Helpers::getSettings();
        if($settings['email']['new_vendor_notification_mail']) {
            $admin = User::role(RoleEnum::ADMIN)->pluck('name')->first();
            return (new MailMessage)
                ->subject('New Store Just Joined!')
                ->greeting("Hi {$admin},")
                ->line("We're thrilled to share some exciting news with you!")
                ->line("A brand new store has joined our platform:")
                ->line("Store Name: {$this->store->store_name}")
                ->line("Discover their incredible products and deals today!")
                ->line("Stay tuned for updates on recent check request approvals and rejections.");
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // for admin
        return [
            'title' => __('notifications.vendor_register_title'),
            'message' => __('notifications.vendor_register_admin',['storeName' => $this->store->store_name]),
            'type' => "store"
        ];
    }
}
