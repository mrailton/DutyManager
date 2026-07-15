<?php

declare(strict_types=1);

namespace App\Http\Controllers\Duties;

use App\Http\Controllers\Controller;
use App\Models\Duty;
use Illuminate\Http\RedirectResponse;

class DeleteDutyController extends Controller
{
    public function __invoke(Duty $duty): RedirectResponse
    {
        $duty->delete();

        return redirect()->route('duties.index')->with('flash', [
            'type' => 'success',
            'message' => 'Duty deleted successfully.',
        ]);
    }
}
