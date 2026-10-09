<?php

namespace App\Http\Requests;

use App\Enums\ApplicationStatus;
use Illuminate\Foundation\Http\FormRequest;

class TransitionMembershipApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_administrator')
            || $this->user()?->hasRole('risk_team')
            || $this->user()?->hasRole('membership_administrator');
    }

    public function rules(): array
    {
        return ['status' => ['required', 'string', 'in:'.implode(',', array_column(ApplicationStatus::cases(), 'value'))], 'note' => ['nullable', 'string', 'max:3000']];
    }
}
