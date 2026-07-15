<?php

declare(strict_types=1);

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UpdateUserController extends Controller
{
    public function __invoke(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)->withoutTrashed()],
        ]);

        $user->update($data);

        return redirect()->route('users.show', $user)->with('flash', [
            'type' => 'success',
            'message' => 'User updated successfully.',
        ]);
    }
}
