<?php

namespace App\Notifications;

use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\User;
use App\SMS\CreateWithdrawRequestSMS;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CreateWithdrawRequestNotification extends Notification
{
    use Queueable;

    public $withdrawRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct($withdrawRequest)
    {
        $this->withdrawRequest = $withdrawRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [CreateWithdrawRequestSMS::class,'database','mail'];
    }

    public function toSend(object $notifiable)
    {
        return (new CreateWithdrawRequestSMS)->sendSMS($notifiable, $this->withdrawRequest);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        $settings = Helpers::getSettings();
        if($settings['email']['withdrawal_request_mail']) {
            $vendor = User::where('id', $this->withdrawRequest->vendor_id)->pluck('name')->first();
            $admin = User::role(RoleEnum::ADMIN)->pluck('name')->first();
            return (new MailMessage)
                ->subject("Withdrawal Request from {$vendor}")
                ->greeting("Hello {$admin},")
                ->line("A withdrawal request has been submitted by {$vendor}.")
                ->line("Requested Amount: {$this->withdrawRequest->amount}")
                ->line("Vendor's Message:")
                ->line($this->withdrawRequest->message)
                ->line("Please review and take appropriate action as necessary.");
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
        $vendor = User::where('id', $this->withdrawRequest->vendor_id)->pluck('name')->first();
        $symbol = Helpers::getDefaultCurrencySymbol();
        return [
            'title' => __('notifications.withdraw_request_title'),
            'message' =>  __('notifications.withdraw_request_admin',['symbol' => $symbol,'withdrawRequest' => $this->withdrawRequest->amount, 'vendor' => $vendor]),
            'type' => "withdraw",
        ];
    }
}
