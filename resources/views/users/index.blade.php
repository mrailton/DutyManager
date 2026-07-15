<x-layout.app>
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Users</h1>
        <button @click="$refs.createModal.showModal()" class="btn btn-primary">Add User</button>
    </div>

    <div class="card bg-base-100 mt-6 shadow-sm">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Last Login</th>
                            <th class="w-20"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="size-8 rounded-full bg-neutral text-neutral-content text-xs">
                                                <span>{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                            </div>
                                        </div>
                                        <div class="font-medium">{{ $user->name }}</div>
                                    </div>
                                </td>
                                <td class="text-sm">{{ $user->email }}</td>
                                <td class="text-sm">
                                    @if ($user->last_login_at)
                                        {{ $user->last_login_at->format('j M Y, H:i') }}
                                    @else
                                        <span class="text-base-content/60">Never</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('users.show', $user) }}" class="btn btn-ghost btn-sm">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-8 text-base-content/60">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="border-t border-base-200 px-4 py-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    <dialog x-ref="createModal" class="modal" x-init="@if ($errors->has('name') || $errors->has('email')) $el.showModal() @endif">
        <div class="modal-box">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
            <h3 class="text-lg font-bold">Add User</h3>
            <form action="{{ route('users.store') }}" method="POST" class="mt-4">
                @csrf
                <label class="fieldset-label">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="input w-full @error('name') input-error @enderror" placeholder="Full name" />
                @error('name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                <label class="fieldset-label mt-4">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="input w-full @error('email') input-error @enderror" placeholder="Email address" />
                @error('email')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                <p class="mt-4 text-sm text-base-content/60">An invitation email will be sent with instructions to set a password.</p>

                <div class="modal-action">
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    @if (session('reactivate_user'))
        <dialog x-ref="reactivateModal" class="modal" x-init="$el.showModal()">
            <div class="modal-box">
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
                </form>
                <h3 class="text-lg font-bold">Account Already Exists</h3>
                <p class="mt-4 text-sm">
                    A user account for <strong>{{ old('email') }}</strong> already exists but has been deleted. You can reactivate it to send a new invitation, or use a different email address.
                </p>
                <div class="modal-action">
                    <form method="dialog">
                        <button class="btn btn-ghost">Use different email</button>
                    </form>
                    <form action="{{ route('users.reactivate', session('reactivate_user')) }}" method="POST">
                        @csrf
                        <input type="hidden" name="name" value="{{ old('name') }}" />
                        <button type="submit" class="btn btn-primary">Reactivate account</button>
                    </form>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>close</button>
            </form>
        </dialog>
    @endif
</x-layout.app>
