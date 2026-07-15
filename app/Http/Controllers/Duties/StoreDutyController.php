<?php

declare(strict_types=1);

namespace App\Http\Controllers\Duties;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDutyRequest;
use App\Models\Duty;
use Illuminate\Http\RedirectResponse;

class StoreDutyController extends Controller
{
    public function __invoke(StoreDutyRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $duty = Duty::create($data);

        if ($data['member_ids'] ?? false) {
            $duty->members()->sync($data['member_ids']);
        }

        if ($data['vehicle_ids'] ?? false) {
            $duty->vehicles()->sync($data['vehicle_ids']);
        }

        return redirect()->route('duties.index')->with('flash', [
            'type' => 'success',
            'message' => 'Duty created successfully.',
        ]);
    }
}
