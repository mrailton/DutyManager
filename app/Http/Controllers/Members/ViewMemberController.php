<?php

declare(strict_types=1);

namespace App\Http\Controllers\Members;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\View\View;

class ViewMemberController extends Controller
{
    public function __invoke(Member $member): View
    {
        $duties = $member->duties()
            ->orderByDesc('duties.start_time')
            ->paginate(10);

        return view('members.show', ['member' => $member, 'duties' => $duties]);
    }
}
