<?php

declare(strict_types=1);

namespace App\Http\Controllers\Duties;

use App\Http\Controllers\Controller;
use App\Models\Duty;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ListDutiesController extends Controller
{
    public function __invoke(Request $request): View
    {
        $duties = Duty::paginate();

        return view('duties.index', ['duties' => $duties]);
    }
}
