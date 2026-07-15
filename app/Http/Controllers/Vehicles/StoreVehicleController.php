<?php

declare(strict_types=1);

namespace App\Http\Controllers\Vehicles;

use App\Enums\VehicleRole;
use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreVehicleController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'callsign' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'in:' . implode(',', array_map(fn ($case) => $case->value, VehicleRole::cases()))],
        ]);

        Vehicle::create($data);

        return redirect()->route('vehicles.index')->with('flash', [
            'type' => 'success',
            'message' => 'Vehicle created successfully.',
        ]);
    }
}
