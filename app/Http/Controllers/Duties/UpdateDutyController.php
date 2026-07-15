<?php

declare(strict_types=1);

namespace App\Http\Controllers\Duties;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDutyRequest;
use App\Models\Duty;
use Illuminate\Http\RedirectResponse;

class UpdateDutyController extends Controller
{
    public function __invoke(StoreDutyRequest $request, Duty $duty): RedirectResponse
    {
        $data = $request->validated();
        $duty->update($data);

        if (array_key_exists('member_ids', $data)) {
            $duty->members()->sync($data['member_ids']);
        }

        if (array_key_exists('vehicle_ids', $data)) {
            $duty->vehicles()->sync($data['vehicle_ids']);
        }

        return redirect()->route('duties.show', $duty)->with('flash', [
            'type' => 'success',
            'message' => 'Duty updated successfully.',
        ]);
    }
}
