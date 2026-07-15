<?php

declare(strict_types=1);

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;

class ListUsersController extends Controller
{
    public function __invoke(): View
    {
        $users = User::paginate();

        return view('users.index', ['users' => $users]);
    }
}
