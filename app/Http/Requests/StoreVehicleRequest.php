<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\VehicleRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'callsign' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'in:' . implode(',', array_map(fn ($case) => $case->value, VehicleRole::cases()))],
        ];
    }
}
