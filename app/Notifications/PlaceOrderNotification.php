<?php

namespace App\Notifications;

use App\Models\Order;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\SMS\PlaceOrderSMS;
use App\Enums\PaymentStatus;
use Illuminate\Bus\Queueable;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PlaceOrderNotification extends Notification
{
    use Queueable;

    private $order;
    private $roleName;
    private $currencySymbol;

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
        return [PlaceOrderSMS::class,'database','mail'];
    }

    public function toSend(object $notifiable)
    {
        switch($this->roleName) {
            case RoleEnum::CONSUMER:
                return $this->toConsumerSMS($notifiable);
            case RoleEnum::VENDOR:
                return $this->toVendorSMS($notifiable);
            case RoleEnum::ADMIN:
                return $this->toAdminSMS($notifiable);
        }
    }

    public function toAdminSMS($notifiable)
    {
        return (new PlaceOrderSMS)->sendSMS($notifiable, $this->roleName, $this->order);
    }

    public function toVendorSMS($notifiable)
    {
        return (new PlaceOrderSMS)->sendSMS($notifiable, $this->roleName, $this->order);
    }

    public function toConsumerSMS($notifiable)
    {
        return (new PlaceOrderSMS)->sendSMS($notifiable, $this->roleName, $this->order);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        $settings = Helpers::getSettings();
        if($settings['email']['order_confirmation_mail']) {
            switch($this->roleName) {
                case RoleEnum::CONSUMER:
                   return $this->toConsumerMail();
                case RoleEnum::VENDOR:
                    return $this->toVendorMail();
                case RoleEnum::ADMIN:
                    return $this->toAdminMail();
            }
        }
    }

    public function toAdminMail(): MailMessage
    {
        $this->currencySymbol = Helpers::getDefaultCurrencySymbol();
        $invoiceData = [
            'order' => $this->order,
            'settings' => Helpers::getSettings(),
        ];

        if ($this->order?->payment_status != PaymentStatus::COMPLETED) {
            return (new MailMessage)
            ->subject("An Order #{$this->order?->order_number} has been placed")
            ->line("Order Status: {$this->order?->order_status?->name}")
            ->line("Order Number:  #{$this->order?->order_number}")
            ->line("Order Payment Status: {$this->order?->payment_status}")
            ->line("Order Amount: {$this->currencySymbol}{$this->order?->total}")
            ->line("Order Date: ".date('d-m-Y', strtotime($this->order?->created_at)));
        } else {
            $invoice = PDF::loadView('emails.invoice', $invoiceData);
            return (new MailMessage)
                ->subject("An Order #{$this->order?->order_number} has been placed")
                ->line("Order Status: {$this->order?->order_status?->name}")
                ->line("Order Number:  #{$this->order?->order_number}")
                ->line("Order Payment Status: {$this->order?->payment_status}")
                ->line("Order Amount: {$this->currencySymbol}{$this->order?->total}")
                ->line("Order Date: ".date('d-m-Y', strtotime($this->order?->created_at)))
                ->attachData($invoice?->output(), "invoice-{$this->order?->order_number}.pdf", [
                    'mime' => 'application/pdf',
                ]);
            ;
        }

    }

    public function toVendorMail(): MailMessage
    {
        $this->currencySymbol = Helpers::getDefaultCurrencySymbol();
        $invoiceData = [
            'order' => $this->order,
            'settings' => Helpers::getSettings(),
        ];

        if ($this->order?->payment_status != PaymentStatus::COMPLETED) {
            return (new MailMessage)
                ->subject("New Order #{$this->order?->order_number} from Your Store")
                ->line("Order Status: {$this->order?->order_status?->name}")
                ->line("Order Number:  #{$this->order?->order_number}")
                ->line("Order Payment Status: {$this->order?->payment_status}")
                ->line("Order Amount: {$this->currencySymbol}{$this->order?->total}")
                ->line("Order Date: ".date('d-m-Y', strtotime($this->order?->created_at)))
                ->line('Thank you for partnering with us!');

        } else {

            $invoice = PDF::loadView('emails.invoice', $invoiceData);
            return (new MailMessage)
                ->subject("New Order #{$this->order?->order_number} from Your Store")
                ->line("Order Status: {$this->order?->order_status?->name}")
                ->line("Order Number:  #{$this->order?->order_number}")
                ->line("Order Payment Status: {$this->order?->payment_status}")
                ->line("Order Amount: {$this->currencySymbol}{$this->order?->total}")
                ->line("Order Date: ".date('d-m-Y', strtotime($this->order?->created_at)))
                ->line('Thank you for partnering with us!')
                ->attachData($invoice?->output(), "invoice-{$this->order?->order_number}.pdf", [
                    'mime' => 'application/pdf',
                ]);
        }

    }

    public function toConsumerMail()
    {
        $this->currencySymbol = Helpers::getDefaultCurrencySymbol();
        $invoiceData = [
            'order' => $this->order,
            'settings' => Helpers::getSettings(),
        ];

        if ($this->order?->payment_status != PaymentStatus::COMPLETED) {
            if ($this->order?->consumer_id) {
                $consumer = Helpers::getConsumerById($this->order?->consumer_id);
                if ($consumer) {
                    return (new MailMessage)
                    ->subject("Your Order #{$this->order?->order_number} details")
                    ->greeting("Hello {$consumer?->name},")
                    ->line("Order Status: {$this->order?->order_status?->name}")
                    ->line("Order Number:  #{$this->order?->order_number}")
                    ->line("Order Payment Status: {$this->order?->payment_status}")
                    ->line("Order Amount: {$this->currencySymbol}{$this->order?->total}")
                    ->line("Order Date: ".date('d-m-Y', strtotime($this->order?->created_at)))
                    ->line("Thank you for being with us.");
                }
            }
        } else {
            $invoice = PDF::loadView('emails.invoice', $invoiceData);
            if ($this->order?->consumer_id) {
                $consumer = Helpers::getConsumerById($this->order?->consumer_id);
                if ($consumer) {
                    return (new MailMessage)
                        ->subject("Your Order #{$this->order?->order_number} Confirmation")
                        ->greeting("Hello {$consumer?->name},")
                        ->line("We're excited to confirm your order with Order #{$this->order?->order_number}.")
                        ->line("Order Status: {$this->order?->order_status?->name}")
                        ->line("Order Number:  #{$this->order?->order_number}")
                        ->line("Order Payment Status: {$this->order?->payment_status}")
                        ->line("Order Amount: {$this->currencySymbol}{$this->order?->total}")
                        ->line("Order Date: ".date('d-m-Y', strtotime($this->order?->created_at)))
                        ->line("Thank you for being with us.")
                        ->attachData($invoice?->output(), "invoice-{$this->order?->order_number}.pdf", [
                            'mime' => 'application/pdf',
                        ]);
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
        if ($this->order?->payment_status != PaymentStatus::COMPLETED) {
            switch($this->roleName) {
                case RoleEnum::CONSUMER:
                    $message = __('notifications.place_order_failed_consumer',['orderNumber' => $this->order?->order_number]);
                    break;
                case RoleEnum::VENDOR:
                    $message = __('notifications.place_order_failed_vendor',['orderNumber' => $this->order?->order_number]);
                    break;
                case RoleEnum::ADMIN:
                    $message = __('notifications.place_order_failed_admin',['orderNumber' => $this->order?->order_number]);
                    break;
            }
        } else {
            switch($this->roleName) {
                case RoleEnum::CONSUMER:
                    $message = __('notifications.place_order_consumer',['orderNumber' => $this->order?->order_number]);
                    break;
                case RoleEnum::VENDOR:
                    $message = __('notifications.place_order_vendor',['orderNumber' => $this->order?->order_number]);
                    break;
                case RoleEnum::ADMIN:
                    $message = __('notifications.place_order_admin',['orderNumber' => $this->order?->order_number]);
                    break;
            }
        }

        return [
            'title' => __('notifications.place_order_title'),
            'message' => $message,
            'type' => "order",
            'order_number' => $this->order?->order_number
        ];
    }
}
