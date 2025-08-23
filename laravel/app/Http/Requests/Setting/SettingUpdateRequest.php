<?php

namespace App\Http\Requests\Setting;

use App\Enums\Setting\SettingKeyEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => [
                'required',
                'string',
                Rule::in(array_column(SettingKeyEnum::cases(), 'value')),
            ],
            'value' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'key.required' => 'Setting key is required.',
            'key.in' => 'Invalid setting key provided.',
            'value.required' => 'Setting value is required.',
        ];
    }
}