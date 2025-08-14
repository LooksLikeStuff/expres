<?php

namespace App\Http\Requests\Chats;

use Illuminate\Foundation\Http\FormRequest;

class MessageSearchRequest extends FormRequest
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
        return [
            'chat_id' => 'required|int|exists:chats,id',
            'term' => 'required|string|max:255',
        ];
    }
}
