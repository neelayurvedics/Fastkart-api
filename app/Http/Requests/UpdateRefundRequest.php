<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\GraphQL\Exceptions\ExceptionHandler;
use Illuminate\Contracts\Validation\Validator;

class UpdateRefundRequest extends FormRequest
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
            'product_id' => ['nullable','exists:products,id,deleted_at,NULL'],
            'payment_type' => ['in:paypal,bank,wallet'],
            'status' => ['required', 'in:pending,approved,rejected'],
        ];
    }

    public function messages()
    {
        return [
            'status.in' => __('validation.refund_status'),
            'payment_type.in' => __('validation.payment_type'),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ExceptionHandler($validator->errors()->first(), 422);
    }
}
