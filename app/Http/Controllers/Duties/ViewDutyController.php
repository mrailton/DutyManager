<?php

declare(strict_types=1);

namespace App\Http\Controllers\Duties;

use App\Http\Controllers\Controller;
use App\Models\Duty;
use App\Models\Member;
use App\Models\Vehicle;
use Illuminate\Contracts\View\View;

class ViewDutyController extends Controller
{
    public function __invoke(Duty $duty): View
    {
        $duty->load(['members', 'vehicles']);
        $members = Member::query()->orderBy('name')->get();
        $vehicles = Vehicle::query()->orderBy('name')->get();

        return view('duties.show', ['duty' => $duty, 'members' => $members, 'vehicles' => $vehicles]);
    }
}
