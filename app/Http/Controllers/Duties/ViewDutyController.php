<?php

declare(strict_types=1);

namespace App\Http\Controllers\Duties;

use App\Http\Controllers\Controller;
use App\Models\Duty;
use Illuminate\Contracts\View\View;

class ViewDutyController extends Controller
{
    public function __invoke(Duty $duty): View
    {
        $duty->load(['members', 'vehicles']);

        return view('duties.show', ['duty' => $duty]);
    }
}
