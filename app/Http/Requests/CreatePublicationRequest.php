<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\GraphQL\Exceptions\ExceptionHandler;
use Illuminate\Contracts\Validation\Validator;

class CreatePublicationRequest extends FormRequest
{
    /**
     * Determine if the user is creatorized to make this request.
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
            'publisher_name'   => ['required', 'string', 'max:255', 'unique:publications,publisher_name,NULL,id,deleted_at,NULL'],
            'country_id' => ['required','exists:countries,id'],
            'state_id' => ['required','exists:states,id'],
            'city' => ['required'],
            'publisher_logo_id' => ['required','exists:attachments,id,deleted_at,NULL'],
            'publisher_cover_image_id' => ['nullable','exists:attachments,id,deleted_at,NULL'],
            'facebook' => ['nullable', 'url'],
            'twitter' => ['nullable', 'url'],
            'instagram' => ['nullable', 'url'],
            'youtube' => ['nullable', 'url'],
            'pinterest' => ['nullable', 'url'],
            'status' => ['nullable','required','min:0','max:1'],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ExceptionHandler($validator->errors()->first(), 422);
    }
}
