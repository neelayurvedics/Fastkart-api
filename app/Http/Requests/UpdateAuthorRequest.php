<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\GraphQL\Exceptions\ExceptionHandler;
use Illuminate\Contracts\Validation\Validator;

class UpdateAuthorRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        $id = $this->route('author') ? $this->route('author')->id : $this->id;
        return [
            'author_name'  => ['nullable', 'max:255', 'unique:authors,author_name,'.$id.',id,deleted_at,NULL'],
            'author_image_id' => ['nullable','exists:attachments,id,deleted_at,NULL'],
            'author_cover_image_id' => ['nullable','exists:attachments,id,deleted_at,NULL'],
            'facebook' => ['nullable', 'url'],
            'twitter' => ['nullable', 'url'],
            'instagram' => ['nullable', 'url'],
            'youtube' => ['nullable', 'url'],
            'pinterest' => ['nullable', 'url'],
            'country_id' => ['exists:countries,id'],
            'state_id' => ['exists:states,id']
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ExceptionHandler($validator->errors()->first(), 422);
    }
}
