<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\GraphQL\Exceptions\ExceptionHandler;
use Illuminate\Contracts\Validation\Validator;
use CodeZero\UniqueTranslation\UniqueTranslationRule;

class CreateCategoryRequest extends FormRequest
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
            'name'  => ['required', 'string', 'max:255', UniqueTranslationRule::for('categories')->where('type', $this->type)->whereNull('deleted_at')],
            'description' => ['nullable','string'],
            'parent_id' => ['nullable','exists:categories,id,deleted_at,NULL'],
            'category_image_id' => ['nullable','exists:attachments,id,deleted_at,NULL'],
            'category_icon_id' => ['nullable','exists:attachments,id,deleted_at,NULL'],
            'status' => ['required','min:0','max:1'],
            'commission_rate' => ['nullable', 'regex:/^([0-9]{1,2}){1}(\.[0-9]{1,2})?$/'],
            'type' => ['required','in:post,product'],
            'is_allow_all_zone' => ['required','min:0','max:1'],
            'exclude_zone_ids.*' => ['nullable','exists:zones,id,deleted_at,NULL'],
        ];
    }

    public function messages()
    {
        return [
            'commission_rate.regex' => __('validation.commission_rate_between'),
            'type.in' => __('validation.category_type_post_or_product'),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ExceptionHandler($validator->errors()->first(), 422);
    }
}
