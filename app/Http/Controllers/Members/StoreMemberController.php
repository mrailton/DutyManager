<?php

declare(strict_types=1);

namespace App\Http\Controllers\Members;

use App\Enums\ClinicalLevel;
use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreMemberController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'clinical_level' => ['required', 'string', 'in:' . implode(',', array_map(fn ($case) => $case->value, ClinicalLevel::cases()))],
        ]);

        Member::create($data);

        return redirect()->route('members.index')->with('flash', [
            'type' => 'success',
            'message' => 'Member created successfully.',
        ]);
    }
}
