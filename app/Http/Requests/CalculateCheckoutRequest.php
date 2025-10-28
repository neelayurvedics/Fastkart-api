<?php

namespace App\Http\Requests;

use App\Helpers\Helpers;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\GraphQL\Exceptions\ExceptionHandler;
use Illuminate\Contracts\Validation\Validator;

class CalculateCheckoutRequest extends FormRequest
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
        return [
            'consumer_id' => ['exists:users,id,deleted_at,NULL', 'nullable'],
            'products' => ['required','array'],
            'products.*.product_id' => ['required','exists:products,id,deleted_at,NULL'],
            'products.*.variation_id' => ['nullable','exists:variations,id,deleted_at,NULL'],
            'coupon' => ['nullable','exists:coupons,code,deleted_at,NULL'],
            'payment_method' => ['required'],
            'billing_address_id' => [Rule::requiredIf(function () {
                return Helpers::isUserLogin();
            }), 'exists:addresses,id,deleted_at,NULL', 'nullable'],
            'shipping_address_id'=>[Rule::requiredIf(function () {
                return ((Helpers::isUserLogin()) && (!Helpers::isDigitalOnly($this->products)));
            }), 'exists:addresses,id,deleted_at,NULL', 'nullable'],
            'shipping_address' => ['array', Rule::requiredIf(function () {
                return ((!Helpers::isUserLogin()) && (!Helpers::isDigitalOnly($this->products)));
            })],
            'billing_address' => ['array', Rule::requiredIf(function () {
                return !(Helpers::isUserLogin());
            })],
            'zone_ids' => ['nullable', 'array'],
            'zone_ids.*' => ['exists:zones,id']
        ];
    }

    public function messages()
    {
        return [
            'coupon.exists' => __('validation.coupon_exists'),
            'shipping_address.required' => __('validation.shipping_address_required'),
            'billing_address.required' => __('validation.billing_address_required'),
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
