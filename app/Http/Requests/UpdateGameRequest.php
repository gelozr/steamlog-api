<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGameRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('games', 'name')->ignore($this->route('id'))
            ],
            'short_description' => [
                'max:500',
            ],
            'genre' => [
                'nullable',
                'string',
                'max:100',
            ],
            'release_date' => [
                'nullable',
                'date',
            ],
            'steam_app_id' => [
                'nullable',
                'numeric',
                Rule::unique('games', 'steam_app_id')->ignore($this->route('id'))
            ],
        ];
    }
}
