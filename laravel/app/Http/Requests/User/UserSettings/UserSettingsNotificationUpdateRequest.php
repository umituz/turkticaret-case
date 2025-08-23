<?php

namespace App\Http\Requests\User\UserSettings;

use Illuminate\Foundation\Http\FormRequest;

class UserSettingsNotificationUpdateRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'email' => 'boolean',
            'push' => 'boolean',
            'sms' => 'boolean',
            'marketing' => 'boolean',
            'orderUpdates' => 'boolean',
            'newsletter' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.boolean' => 'Email notification preference must be true or false.',
            'push.boolean' => 'Push notification preference must be true or false.',
            'sms.boolean' => 'SMS notification preference must be true or false.',
            'marketing.boolean' => 'Marketing notification preference must be true or false.',
            'orderUpdates.boolean' => 'Order update notification preference must be true or false.',
            'newsletter.boolean' => 'Newsletter notification preference must be true or false.',
        ];
    }
}