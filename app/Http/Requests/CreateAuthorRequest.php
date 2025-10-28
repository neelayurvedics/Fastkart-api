<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\GraphQL\Exceptions\ExceptionHandler;
use Illuminate\Contracts\Validation\Validator;

class CreateAuthorRequest extends FormRequest
{
    /**
     * Determine if the user is creatorized to make this request.
     *
     * @return bool
     */
    public function creatorize()
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
            'author_name'   => ['required', 'string', 'max:255', 'unique:authors,author_name,NULL,id,deleted_at,NULL'],
            'bio' => ['nullable', 'min:10'],
            'country_id' => ['required','exists:countries,id'],
            'state_id' => ['required','exists:states,id'],
            'city' => ['required'],
            'birth_date' => ['nullable', 'date'],
            'death_date' => ['nullable','date', 'after:birth_date'],
            'author_image_id' => ['required','exists:attachments,id,deleted_at,NULL'],
            'author_cover_image_id' => ['nullable','exists:attachments,id,deleted_at,NULL'],
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
