<?php

declare(strict_types=1);

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class DeleteUserController extends Controller
{
    public function __invoke(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('flash', [
                'type' => 'danger',
                'message' => 'You cannot delete your own account.',
            ]);
        }

        $user->delete();

        return redirect()->route('users.index')->with('flash', [
            'type' => 'success',
            'message' => 'User deleted successfully.',
        ]);
    }
}
