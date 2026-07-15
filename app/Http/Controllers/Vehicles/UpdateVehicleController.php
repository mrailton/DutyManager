<?php

declare(strict_types=1);

namespace App\Http\Controllers\Vehicles;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;

class UpdateVehicleController extends Controller
{
    public function __invoke(StoreVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update($request->validated());

        return redirect()->route('vehicles.index')->with('flash', [
            'type' => 'success',
            'message' => 'Vehicle updated successfully.',
        ]);
    }
}
