<?php

declare(strict_types=1);

namespace App\Http\Controllers\Vehicles;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ListVehiclesController extends Controller
{
    public function __invoke(Request $request): View
    {
        $vehicles = Vehicle::paginate();

        return view('vehicles.list', ['vehicles' => $vehicles]);
    }
}
