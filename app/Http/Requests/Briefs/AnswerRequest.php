<?php

namespace App\Http\Requests\Briefs;

use Illuminate\Foundation\Http\FormRequest;

class AnswerRequest extends FormRequest
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
            'answers'      => 'nullable|array',
            'price'        => 'nullable|numeric',
            'rooms'        => 'nullable|array',
            'addRooms' => 'nullable|array',
            'documents'    => 'nullable|array',
            'documents.*'  => 'file|mimes:pdf,xlsx,xls,doc,docx,jpg,jpeg,png,heic,heif,mp4,mov,avi,wmv,flv,mkv,webm,3gp',
            'skip_page'    => 'nullable|boolean'
        ];
    }
}
