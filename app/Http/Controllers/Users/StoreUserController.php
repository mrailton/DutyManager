<?php

declare(strict_types=1);

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\UserInvited;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StoreUserController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
        ]);

        $deletedUser = User::onlyTrashed()->where('email', $data['email'])->first();

        if ($deletedUser) {
            return back()->withInput()->with('reactivate_user', $deletedUser->id)->with('flash', [
                'type' => 'warning',
                'message' => 'An account with that email already exists but was deleted.',
            ]);
        }

        $exists = User::where('email', $data['email'])->exists();

        if ($exists) {
            return back()->withErrors(['email' => 'A user with this email address already exists.'])->withInput();
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make(Str::random(60)),
        ]);

        $user->notify(new UserInvited());

        return redirect()->route('users.index')->with('flash', [
            'type' => 'success',
            'message' => 'User created. An invitation email has been sent.',
        ]);
    }
}
