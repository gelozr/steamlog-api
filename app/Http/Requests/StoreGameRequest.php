<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGameRequest extends FormRequest
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
                'required',
                'string',
                'max:255',
                Rule::unique('games', 'name')
            ],
            'short_description' => [
                'max:500',
            ],
            'genre' => [
                'string',
                'max:100',
            ],
            'release_date' => [
                'date',
            ],
            'steam_app_id' => [
                'nullable',
                'numeric',
                Rule::unique('games', 'steam_app_id')
            ],
        ];
    }
}
