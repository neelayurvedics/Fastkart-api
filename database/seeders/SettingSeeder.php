<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Helpers\Helpers;
use App\Models\Currency;
use App\Models\language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    protected $baseName;

    public function __construct()
    {
        $this->baseName = config('app.name');
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        $currency_id = Currency::where('status', true)->first()?->id;
        $locale_id = language::where('status', true)->first()?->id;
        $values = [
            'general' => [
                'light_logo_image_id' => Helpers::getAttachmentId('logo-white.png'),
                'dark_logo_image_id' => Helpers::getAttachmentId('logo-dark.png'),
                'tiny_logo_image_id' => Helpers::getAttachmentId('tiny-logo.png'),
                'favicon_image_id' => Helpers::getAttachmentId('favicon.png'),
                'site_title' => $this->baseName.' Marketplace: Where Vendors Shine Together',
                'site_tagline' => "Shop Unique, Sell Exceptional – ".$this->baseName."'s Multi-Vendor Universe.",
                'site_name' => $this->baseName,
                'site_url' => '',
                'default_timezone' => 'Asia/Kolkata',
                'default_currency_id' => $currency_id,
                'default_language_id' => $locale_id,
                'admin_site_language_direction' => 'ltr',
                'min_order_amount' => 0,
                'min_order_free_shipping' => 50,
                'product_sku_prefix' => 'FS',
                'mode' => 'light-only',
                'google_map_api_key' => 'Enter Google Map API Key',
                'copyright' => 'Copyright 2024 © '.$this->baseName.' theme by pixelstrapp',
            ],
            'activation' => [
                'multivendor' => true,
                'point_enable' => true,
                'coupon_enable' => true,
                'wallet_enable' => true,
                'stock_product_hide' => false,
                'store_auto_approve' => true,
                'product_auto_approve' => true,
                'guest_checkout' => true,
                'track_order' => true,
                'login_number' => true,
                'send_sms' => true,
                'zone_enable' => true
            ],
            'wallet_points' => [
                'signup_points' => 100,
                'min_per_order_amount' => 100,
                'point_currency_ratio' => 30,
                'reward_per_order_amount' => 10,
            ],
            'vendor_commissions' => [
                'status' => true,
                'min_withdraw_amount' => 500,
                'default_commission_rate' => 10,
                'is_category_based_commission' => true,
            ],
            'email' => [
                'mail_host' => 'ENTER_YOUR_HOST',
                'mail_port' => 465,
                'mail_mailer' => 'smtp',
                'mail_username' => 'ENTER_YOUR_USERNAME',
                'mail_password' => 'ENTER_YOUR_PASSWORD',
                'mail_encryption' => 'ssl',
                'mail_from_name' => 'no-reply',
                'mail_from_address' => 'ENTER_YOUR_EMAIL@MAIL.COM',
                'mailgun_domain' => 'ENTER_YOUR_MAILGUN_DOMAIN',
                'mailgun_secret' => 'ENTER_YOUR_MAILGUN_SECRET',
                'system_test_mail' => true,
                'password_reset_mail' => true,
                'visitor_inquiry_mail' => true,
                'cancel_order_mail' => true,
                'refund_request_mail' => true,
                'withdrawal_request_mail' => true,
                'pending_order_alert_mail' => true,
                'order_confirmation_mail' => true,
                'signup_welcome_mail' => true,
                'order_status_update_mail' => true,
                'refund_status_update_mail' => true,
                'withdrawal_status_update_mail' => true,
                'new_vendor_notification_mail' => true,
            ],
            'media_configuration' => [
                'media_disk' => 'public',
                'aws_access_key_id' => 'ENTER_YOUR_AWS_ACCESS_KEY',
                'aws_secret_access_key' => 'ENTER_YOUR_AWS_SECRET_KEY',
                'aws_bucket' => 'ENTER_YOUR_AWS_BUCKET',
                'aws_default_region' => 'ENTER_YOUR_AWS_DEFAULT_REGION',
            ],
            'refund' => [
                'status' => true,
                "refundable_days" => 7,
            ],
            'delivery' => [
                'default_delivery'=> 1,
                'default' => [
                    'title' => 'Standard Delivery',
                    'description' => 'Approx 5 to 7 Days'
                ],
                'same_day_delivery' => true,
                'same_day' => [
                    'title' => 'Express Delivery',
                    'description' => 'Schedule'
                ],
                'same_day_intervals' => [
                    [
                        'title' => 'Morning',
                        'description' => '8.00 AM - 12.00 AM',
                    ],
                    [
                        'title' => 'Noon',
                        'description' => '12.00 PM - 2.00 PM'
                    ],
                    [
                        'title' => 'Afternoon',
                        'description' => '02.00 PM - 05.00 PM',
                    ],
                    [
                        'title' => 'Evening',
                        'description' => '05.00 PM - 08.00 PM'
                    ]
                ]
            ],
            'payment_methods' => [
                'cod' => [
                    'title' => 'Cash On Delivery',
                    'status' => true
                ],
                'paypal' => [
                    'title' => 'PayPal',
                    'client_id' => 'YOUR_PAYPAL_CLIENT_ID',
                    'client_secret' => 'YOUR_PAYPAL_CLIENT_SECRET',
                    'status' => true,
                    'sandbox_mode' => true,
                ],
                'stripe' => [
                    'title' => 'Stripe',
                    'key' => 'YOUR_STRIPE_KEY',
                    'secret' => 'YOUR_STRIPE_SECRET',
                    'status' => true,
                ],
                'razorpay' => [
                    'title' => 'RazorPay',
                    'key' => 'YOUR_RAZORPAY_KEY',
                    'secret' => 'YOUR_RAZORPAY_SECRET',
                    'status' => true
                ],
                'mollie' => [
                    'title' => 'Mollie',
                    'secret_key' => 'YOUR_MOLLIE_SECRET_KEY',
                    'status' => true,
                ],
                'ccavenue' => [
                    'title' => 'CCAvenue',
                    'merchant_id' => 'ENTER_YOUR_MERCHANT_ID',
                    'working_key' => 'ENTER_YOUR_WORKING_KEY',
                    'access_code' => 'ENTER_YOUR_ACCESS_CODE',
                    'status' => true,
                    'sandbox_mode' => true,
                ],
                'phonepe' => [
                    'title' => 'PhonePe',
                    'merchant_id' => 'ENTER_YOUR_MERCHANT_ID',
                    'salt_key' => 'ENTER_YOUR_SALT_KEY',
                    'salt_index' => 'ENTER_YOUR_SALT_INDEX',
                    'status' => true,
                    'sandbox_mode' => true,
                ],
                'instamojo' => [
                    'title' => 'InstaMojo',
                    'client_id' => 'ENTER_YOUR_CLIENT_ID',
                    'client_secret' => 'ENTER_YOUR_CLIENT_SECRET',
                    'salt_key' => 'ENTER_YOUR_SALT_KEY',
                    'status' => true,
                    'sandbox_mode' => true,
                ],
                'bkash' => [
                    'title' => 'bKash',
                    'app_key' => 'ENTER_YOUR_APP_KEY',
                    'app_secret' => 'ENTER_YOUR_APP_SECRET',
                    'username' => 'ENTER_YOUR_BKASH_USERNAME',
                    'password' => 'ENTER_YOUR_BKASH_PASSWORD',
                    'status' => true,
                    'sandbox_mode' => true,
                ],
                'flutter_wave' => [
                    'title' => 'FlutterWave',
                    'public_key' => 'ENTER_YOUR_PUBLIC_KEY',
                    'secret_key' => 'ENTER_YOUR_SECRET_KEY',
                    'secret_hash' => 'ENTER_YOUR_SECRET_HASH',
                    'status' => true,
                    'sandbox_mode' => true,
                ],
                'paystack' => [
                    'title' => 'Paystack',
                    'public_key' => 'ENTER_YOUR_PUBLIC_KEY',
                    'secret_key' => 'ENTER_YOUR_SECRET_KEY',
                    'status' => true,
                    'sandbox_mode' => true,
                ],
                'sslcommerz' => [
                    'title' => 'SSLCommerz',
                    'store_id' => 'ENTER_YOUR_STORE_ID',
                    'store_password' => 'ENTER_YOUR_STORE_PASSWORD',
                    'status' => true,
                    'sandbox_mode' => true,
                ],
                'bank_transfer' => [
                    'title' => 'Bank Transfer',
                    'status' => true,
                ],
            ],
            'google_reCaptcha' => [
                'secret' => 'ENTER_YOUR_SECRET_KEY',
                'site_key' => 'ENTER_YOUR_SITE_KEY',
                'status' => false,
            ],
            'analytics' => [
                'facebook_pixel' => [
                    'pixel_id' => 'YOUR_PIXEL_ID',
                    'status' => false,
                ],
                'google_analytics' => [
                    'measurement_id' => 'ENTER_YOUR_SECRET_KEY',
                    'status' => false,
                ]
            ],
            'maintenance' => [
                'title' => "We'll be back Soon..",
                'maintenance_mode' => false,
                'end_date' => null,
                'maintenance_image_id' => Helpers::getAttachmentId('maintainance.jpg'),
                'description' => "We are busy to updating our store for you.",
                'start_date' => null
            ],
            'sms_methods' => [
                'default_sms_method' => 'twilio',
                'config' => [
                    'cancel_order_sms' => true,
                    'refund_request_sms' => true,
                    'withdraw_request_sms' => true,
                    'pending_order_sms' => true,
                    'place_order_sms' => true,
                    'signup_bonus_sms' => true,
                    'update_order_status_sms' => true,
                    'update_refund_request_sms' => true,
                    'update_withdraw_request_sms' => true,
                    'vendor_register_sms' => true,
                ],
                'twilio' => [
                    'title' => 'Twilio',
                    'status' => true,
                    'twilio_sid' => 'ENTER_YOUR_TWILIO_SID',
                    'twilio_auth_token' => 'ENTER_YOUR_TWILIO_AUTH_TOKEN',
                    'twilio_number' => 'ENTER_YOUR_TWILIO_NUMBER',
                ]
            ]
        ];

        Setting::updateOrCreate(['values' => $values]);
        DB::table('seeders')->updateOrInsert([
            'name' => 'SettingSeeder',
            'is_completed' => true
        ]);
    }
}