<?php

namespace App\Http\Requests\Notifications;

use App\Models\SystemNotification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SnoozeNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $notification = $this->route('systemNotification');

        return $notification instanceof SystemNotification
            && ($this->user()?->can('snooze notifications', $notification) ?? false);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'duration' => ['required', Rule::in(['1_hour', '1_day'])],
        ];
    }
}
