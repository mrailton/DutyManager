<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Contracts\View\View;

class MembersController extends Controller
{
    public function index(): View
    {
        $members = Member::all();

        return view('members.index', compact('members'));
    }
}
