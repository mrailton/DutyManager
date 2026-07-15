<x-layout.app>
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('users.index') }}" class="text-sm text-base-content/60 hover:text-base-content">&larr; Back to Users</a>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white mt-1">{{ $user->name }}</h1>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title">Details</h2>
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-base-content/60">Email</dt>
                            <dd class="font-medium">{{ $user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-base-content/60">Last Login</dt>
                            <dd>
                                @if ($user->last_login_at)
                                    <span class="font-medium">{{ $user->last_login_at->format('j F Y, H:i') }}</span>
                                @else
                                    <span class="text-base-content/60">Never</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-base-content/60">Joined</dt>
                            <dd class="font-medium">{{ $user->created_at->format('j F Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-base-content/60">Status</dt>
                            <dd>
                                @if ($user->trashed())
                                    <span class="badge badge-soft badge-error">Deleted</span>
                                @else
                                    <span class="badge badge-soft badge-success">Active</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title">Edit User</h2>
                    <form action="{{ route('users.update', $user) }}" method="POST" class="mt-4">
                        @csrf
                        @method('PUT')
                        <label class="fieldset-label">Name</label>
                        <input type="text" name="name" value="{{ $user->name }}" required class="input w-full" />

                        <label class="fieldset-label mt-4">Email</label>
                        <input type="email" name="email" value="{{ $user->email }}" required class="input w-full" />

                        <div class="mt-6 flex items-center gap-3">
                            <button type="submit" class="btn btn-primary">Save Changes</button>

                            @if (!$user->trashed() && $user->id !== auth()->id())
                                <button @click="$refs.deleteModal.showModal()" type="button" class="btn btn-outline btn-error">Delete User</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <dialog x-ref="deleteModal" class="modal">
        <div class="modal-box">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
            <h3 class="text-lg font-bold">Delete User</h3>
            <p class="mt-4 text-sm">Are you sure you want to delete <strong>{{ $user->name }}</strong>? This action can be undone by an administrator.</p>
            <form action="{{ route('users.delete', $user) }}" method="POST" class="modal-action">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-ghost" onclick="this.closest('dialog').close()">Cancel</button>
                <button type="submit" class="btn btn-error">Delete</button>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</x-layout.app>
