<?php

declare(strict_types=1);

namespace App\Http\Controllers\Vehicles;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\View\View;

class ViewVehicleController extends Controller
{
    public function __invoke(Vehicle $vehicle): View
    {
        $vehicle->load('duties');

        return view('vehicles.show', ['vehicle' => $vehicle]);
    }
}
