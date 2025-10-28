<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatelanguageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('language') ? $this->route('language')->id : $this->id;
        return [
            'name' => ['nullable', 'unique_translation:languages,name,'.$id.',id,deleted_at,NULL'],
            'locale' => ['nullable']
        ];
    }
}
