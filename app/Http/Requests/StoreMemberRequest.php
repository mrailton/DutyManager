<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ClinicalLevel;
use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'clinical_level' => ['required', 'string', 'in:' . implode(',', array_map(fn ($case) => $case->value, ClinicalLevel::cases()))],
            'driver' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'driver' => $this->boolean('driver'),
        ]);
    }
}
