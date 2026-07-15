<?php

declare(strict_types=1);

namespace App\Http\Controllers\Members;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberRequest;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;

class UpdateMemberController extends Controller
{
    public function __invoke(StoreMemberRequest $request, Member $member): RedirectResponse
    {
        $member->update($request->validated());

        return redirect()->route('members.index')->with('flash', [
            'type' => 'success',
            'message' => 'Member updated successfully.',
        ]);
    }
}
