<?php

namespace App\Http\Requests\Chats;

use App\Enums\ChatType;
use Illuminate\Foundation\Http\FormRequest;

class CreateChatRequest extends FormRequest
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
            'type' => ['required', 'in:' . implode(',', array_map(fn($case) => $case->value, ChatType::cases()))],
            'title' => ['nullable', 'required_if:type,' . ChatType::GROUP->value],
            'avatar' => ['nullable', 'file', 'mimetypes:image/*', 'max:2048'],
            'participants' => 'required|array',
            'participants.*' => 'integer|exists:users,id'
        ];
    }
}
