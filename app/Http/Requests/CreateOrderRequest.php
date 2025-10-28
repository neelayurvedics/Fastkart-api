<?php

namespace App\Http\Requests;

use App\Helpers\Helpers;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\GraphQL\Exceptions\ExceptionHandler;
use Illuminate\Contracts\Validation\Validator;

class CreateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [
            'consumer_id' => ['nullable','exists:users,id,deleted_at,NULL'],
            'products' => ['required','array'],
            'products.*.product_id' => ['required','exists:products,id,deleted_at,NULL'],
            'products.*.variation_id' => ['nullable','exists:variations,id,deleted_at,NULL'],
            'coupon' => ['nullable','exists:coupons,code,deleted_at,NULL'],
            'billing_address_id' => [Rule::requiredIf(function () {
                return Helpers::isUserLogin();
            }), 'exists:addresses,id,deleted_at,NULL', 'nullable'],
            'shipping_address_id'=>[Rule::requiredIf(function () {
                return (Helpers::isUserLogin() && Helpers::isPhysicalOnly($this->products));
            }), 'exists:addresses,id,deleted_at,NULL', 'nullable'],
            'shipping_address' => ['array', Rule::requiredIf(function () {
                return ((!Helpers::isUserLogin()) && Helpers::isPhysicalOnly($this->products));
            })],
            'billing_address' => ['array', Rule::requiredIf(function () {
                return !(Helpers::isUserLogin());
            })],
            'payment_method' => ['string', 'in:razorpay,paystack,flutter_wave,phonepe,sslcommerz,instamojo,paypal,stripe,mollie,bank_transfer,bkash,ccavenue,cod'],
            'delivery_interval' => ['nullable','string'],
            'name' => [
                Rule::requiredIf(function () {
                    return !(Helpers::isUserLogin());
                })
            ],
            'email' => [
                Rule::requiredIf(function () {
                    return !(Helpers::isUserLogin());
                }),
            ],
            'create_account' => [
                Rule::requiredIf(function () {
                    return !(Helpers::isUserLogin());
                }),'min:0','max:1', 'nullable'
            ],
            'country_code' => [
                Rule::requiredIf(function () {
                    return !(Helpers::isUserLogin());
                }), 'nullable'
            ],
            'phone' => [
                Rule::requiredIf(function () {
                    return !(Helpers::isUserLogin());
                }), 'digits_between:6,15','unique:users,phone,NULL,id,deleted_at,NULL', 'nullable'
            ],
            'password' => ['required_if:create_account,1', 'min:8',  'nullable'],
            'password_confirmation' => ['required_if:create_account,1', 'same:password', 'nullable'],
            'zone_ids' => ['nullable', 'array'],
            'zone_ids.*' => ['exists:zones,id']
        ];

        if ($this->create_account) {
            return array_merge($rules, [
                'email' => ['required', 'unique:users,email,NULL,id,deleted_at,NULL'],
                'phone' => ['required', 'digits_between:6,15','unique:users,phone,NULL,id,deleted_at,NULL'],
            ]);
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'coupon.exists' => __('validation.coupon_exists'),
            'create_account.required' => __('validation.guest_create_account_required'),
            'name.required' => __('validation.guest_checkout_name_required'),
            'email.required' => __('validation.guest_email_required'),
            'country_code.required' => __('validation.guest_country_code_required'),
            'phone.required' => __('validation.guest_phone_field_required'),
            'shipping_address.required' => __('validation.guest_shipping_address_required'),
            'billing_address.required' => __('validation.guest_billing_address_required'),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ExceptionHandler($validator->errors()->first(), 422);
    }

    protected function prepareForValidation()
    {
        if (!Helpers::isUserLogin()) {
            if (!Helpers::isGuestCheckoutEnabled()) {
                throw new ExceptionHandler(__('errors.checkout_feature_disabled'), 422);
            }
        }
    }
}
