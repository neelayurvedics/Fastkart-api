<?php

namespace App\Notifications;

use App\Models\User;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use Illuminate\Bus\Queueable;
use App\SMS\CreateRefundRequestSMS;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CreateRefundRequestNotification extends Notification
{
    use Queueable;

    private $refund;

    /**
     * Create a new notification instance.
     */
    public function __construct($refund)
    {
        $this->refund = $refund;
    }

     /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [CreateRefundRequestSMS::class,'database','mail'];
    }

    public function toSend(object $notifiable)
    {
        $consumer = User::where('id', $this->refund->consumer_id)->pluck('name')->first();
        return (new CreateRefundRequestSMS)->sendSMS($notifiable, $consumer);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        $settings = Helpers::getSettings();
        if($settings['email']['refund_request_mail']) {
            $consumer = User::where('id', $this->refund->consumer_id)->pluck('name')->first();
            $admin = User::role(RoleEnum::ADMIN)->pluck('name')->first();
            return (new MailMessage)
                ->subject("Refund Request from {$consumer}")
                ->greeting("Hello {$admin},")
                ->line("A refund request has been submitted by {$consumer}.")
                ->line("Requested Amount: {$this->refund->amount}")
                ->line("Reason for Refund: {$this->refund->reason}")
                ->line("Your attention to this matter is greatly appreciated.");
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        //for admin
        $consumer = User::where('id', $this->refund->consumer_id)->pluck('name')->first();
        return [
            'title' => __('notifications.refund_request_title'),
            'message' => __('notifications.refund_request_admin',['consumer' => $consumer]),
            'type' => 'refund',
        ];
    }
}
