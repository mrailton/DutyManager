<x-layout.app>
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Members</h1>
        <button @click="$refs.createModal.showModal()" class="btn btn-primary">Add Member</button>
    </div>

    <div class="card bg-base-100 mt-6 shadow-sm">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Clinical Level</th>
                            <th class="w-20"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $member)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div>
                                            <div class="font-medium">{{ $member->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $member->clinical_level->badgeClass() }}">{{ $member->clinical_level->label() }}</span>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-ghost btn-sm">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-8 text-base-content/60">
                                    No members found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($members->hasPages())
                <div class="border-t border-base-200 px-4 py-3">
                    {{ $members->links() }}
                </div>
            @endif
        </div>
    </div>

    <dialog x-ref="createModal" class="modal">
        <div class="modal-box">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
            <h3 class="text-lg font-bold">Add Member</h3>
            <form action="{{ route('members.store') }}" method="POST" class="mt-4">
                @csrf
                <label class="fieldset-label">Name</label>
                <input type="text" name="name" required class="input w-full" placeholder="Full name" />

                <label class="fieldset-label mt-4">Clinical Level</label>
                <select name="clinical_level" required class="select w-full">
                    <option value="" disabled selected>Select level</option>
                    @foreach (App\Enums\ClinicalLevel::cases() as $level)
                        <option value="{{ $level->value }}">{{ $level->label() }}</option>
                    @endforeach
                </select>

                <div class="modal-action">
                    <button type="submit" class="btn btn-primary">Create Member</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</x-layout.app>
