<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDutyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'organiser' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'covered' => ['boolean'],
            'notes' => ['nullable', 'string'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['exists:members,id'],
            'vehicle_ids' => ['nullable', 'array'],
            'vehicle_ids.*' => ['exists:vehicles,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'covered' => $this->boolean('covered'),
        ]);
    }
}
