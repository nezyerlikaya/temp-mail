<?php

namespace App\Http\Requests\Notifications;

use App\Models\SystemNotification;
use Illuminate\Foundation\Http\FormRequest;

class MarkNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $notification = $this->route('systemNotification');

        return $notification instanceof SystemNotification
            && ($this->user()?->can('mark notification', $notification) ?? false);
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [];
    }
}
