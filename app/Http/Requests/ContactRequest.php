<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'    => 'required|string|min:2|max:100',
            'phone'   => 'required|regex:/^\+?\d{7,15}$/',
            'email'   => 'required|email',
            'comment' => 'required|string|min:10|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'phone_regex' => 'Телефон должен содержать 7–15 цифр, можно с плюсом.',
        ];
    }
}