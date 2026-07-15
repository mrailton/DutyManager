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
                            <th>Driver</th>
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
                                    @if($member->driver)
                                        <span class="badge badge-success">Driver</span>
                                    @else
                                        <span class="badge badge-warning">Non-Driver</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('members.show', $member) }}" class="btn btn-ghost btn-sm">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-8 text-base-content/60">
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

    <x-modal name="createModal" title="Add Member">
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

            <div class="mt-4 flex items-center gap-2">
                <input type="checkbox" name="driver" value="1" class="checkbox" id="driver-checkbox" />
                <label for="driver-checkbox" class="fieldset-label">Driver</label>
            </div>

            <div class="modal-action">
                <button type="submit" class="btn btn-primary">Create Member</button>
            </div>
        </form>
    </x-modal>

    <div x-data="{ editItem: { id: null, name: '', clinical_level: '', driver: false } }">
        <x-modal name="editModal" title="Edit Member">
            <form :action="`/members/${editItem.id}`" method="POST" class="mt-4">
                @csrf
                @method('PUT')
                <label class="fieldset-label">Name</label>
                <input type="text" name="name" required class="input w-full" x-model="editItem.name" />

                <label class="fieldset-label mt-4">Clinical Level</label>
                <select name="clinical_level" required class="select w-full" x-model="editItem.clinical_level">
                    <option value="" disabled>Select level</option>
                    @foreach (App\Enums\ClinicalLevel::cases() as $level)
                        <option value="{{ $level->value }}">{{ $level->label() }}</option>
                    @endforeach
                </select>

                <div class="mt-4 flex items-center gap-2">
                    <input type="checkbox" name="driver" value="1" class="checkbox" id="edit-driver-checkbox" x-model="editItem.driver" />
                    <label for="edit-driver-checkbox" class="fieldset-label">Driver</label>
                </div>

                <div class="modal-action">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </x-modal>
    </div>
</x-layout.app>
