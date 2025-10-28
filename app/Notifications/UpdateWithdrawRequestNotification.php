<?php

namespace App\Notifications;

use App\Models\User;
use App\Helpers\Helpers;
use Illuminate\Bus\Queueable;
use App\SMS\UpdateWithdrawRequestSMS;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class UpdateWithdrawRequestNotification extends Notification
{
    use Queueable;

    private static $withdrawRequest;

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
        return [UpdateWithdrawRequestSMS::class,'database','mail'];
    }

    public function toSend(object $notifiable)
    {
        return (new UpdateWithdrawRequestSMS)->sendSMS($notifiable, $this->withdrawRequest);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        $settings = Helpers::getSettings();
        if($settings['email']['withdrawal_status_update_mail']) {
            $vendor = User::where('id', $this->withdrawRequest->vendor_id)->pluck('name')->first();
            return (new MailMessage)
                ->subject('withdraw Request Status Updated')
                ->greeting('hello ' . $vendor . ',')
                ->line('We would like to inform you that the status of your refund request has been updated:')
                ->line('Your refund request for ' . $this->withdrawRequest->amount . ' has been ' . $this->withdrawRequest->status . '.')
                ->line('If you require any further assistance, please don’t hesitate to contact us.')
                ->line('Thank you.');
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // for vendor
        $symbol = Helpers::getDefaultCurrencySymbol();
        return [
            'title' => __('notifications.updated_withdraw_request_title',['withdrawRequestStatus' => $this->withdrawRequest->status]),
            'message' => __('notifications.updated_withdraw_request_vendor',['symbol' => $symbol, 'withdrawRequest' => $this->withdrawRequest->amount, 'withdrawRequestStatus' => '$this->withdrawRequest->status']),
            'type' => "withdraw"
        ];
    }
}
