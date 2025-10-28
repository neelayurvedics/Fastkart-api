<?php

namespace App\Notifications;

use App\Helpers\Helpers;
use Illuminate\Bus\Queueable;
use App\SMS\SignUpBonusPointsSMS;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SignUpBonusPointsNotification extends Notification
{
    use Queueable;

    private $user;

    /**
     * Create a new notification instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [SignUpBonusPointsSMS::class,'database','mail'];
    }

    public function toSend(object $notifiable)
    {
        return (new SignUpBonusPointsSMS)->sendSMS($notifiable);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        $settings = Helpers::getSettings();
        if($settings['email']['signup_welcome_mail']) {
            $settings = Helpers::getSettings();
            return (new MailMessage)
                ->subject('Congratulations on Your Sign-Up Bonus!')
                ->greeting('Hello ' . $this->user->name . ',')
                ->line('Woohoo! You\'ve just received ' . $settings['wallet_points']['signup_points'] . ' bonus points as a thank you for joining us!')
                ->line('Keep exploring, shopping, and enjoying our platform. Your points are just the beginning of a fantastic experience!')
                ->line('Thank you for becoming a part of our platform. Here\'s to exciting times ahead!')
                ->line('Enjoy your rewards!');
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        //for consumer
        $settings = Helpers::getSettings();
        return [
            'title' => __('notifications.signup_bonus_points_title'),
            'message' => __('notifications.signup_bonus_points_consumer', ['signUpPoints' => $settings['wallet_points']['signup_points']]),
            'type' => "points"
        ];
    }
}
