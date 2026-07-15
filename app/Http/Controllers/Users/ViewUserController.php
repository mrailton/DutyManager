<?php

declare(strict_types=1);

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;

class ViewUserController extends Controller
{
    public function __invoke(User $user): View
    {
        return view('users.show', ['user' => $user]);
    }
}
