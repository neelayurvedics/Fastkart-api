<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Request;
use Illuminate\Foundation\Http\FormRequest;
use App\GraphQL\Exceptions\ExceptionHandler;
use Illuminate\Contracts\Validation\Validator;

class CreateShippingRuleRequest extends FormRequest
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
        $shippingRules = [
            'name'  => ['required', 'max:255'],
            'rule_type' => ['required', 'in:base_on_price,base_on_weight'],
            'shipping_type' => ['required', 'in:free,fixed,percentage'],
            'min' => ['required', 'numeric', 'min:0'],
            'max' => ['required', 'numeric', 'min:' . (float)$this->input('min')],
            'status' => ['required','min:0','max:1'],
            'shipping_id' => ['required', 'exists:shippings,id,deleted_at,NULL'],
        ];

        if (Request::input('shipping_type') == 'percentage') {
          return array_merge($shippingRules, ['amount' => ['required', 'regex:/^([0-9]{1,2}){1}(\.[0-9]{1,2})?$/']]);
        }

        return $shippingRules;
    }

    public function messages()
    {
        return [
            'amount.regex' => __('validation.amount_percent_between'),
            'shipping_type.in' => __('validation.shipping_type'),
            'rule_type.in' => __('validation.shipping_rule'),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ExceptionHandler($validator->errors()->first(), 422);
    }
}
