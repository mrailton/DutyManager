<?php

declare(strict_types=1);

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\UserInvited;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReactivateUserController extends Controller
{
    public function __invoke(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user->restore();
        $user->update(['name' => $data['name']]);

        $user->notify(new UserInvited());

        return redirect()->route('users.index')->with('flash', [
            'type' => 'success',
            'message' => 'User account reactivated. An invitation email has been sent.',
        ]);
    }
}
