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
        $member->load('duties');

        return view('members.show', ['member' => $member]);
    }
}
