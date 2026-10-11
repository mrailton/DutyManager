<x-layout.app>
    <div x-data="{ editItem: { id: {{ $member->id }}, name: '{{ $member->name }}', clinical_level: '{{ $member->clinical_level->value }}', driver: {{ $member->driver ? 'true' : 'false' }} } }">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('members.index') }}" class="text-sm text-base-content/60 hover:text-base-content">&larr; Back to Members</a>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white mt-1">{{ $member->name }}</h1>
            </div>
            <button @click="$refs.editModal.showModal()" class="btn btn-outline btn-primary">Edit</button>
        </div>

        <section>
            <div class="lg:col-span-2 py-6">
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title">Details</h2>
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm text-base-content/60">Clinical Level</dt>
                                <dd>
                                    <span class="badge {{ $member->clinical_level->badgeClass() }}">{{ $member->clinical_level->label() }}</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-base-content/60">Driver</dt>
                                <dd>
                                    @if ($member->driver)
                                        <span class="badge badge-success">Driver</span>
                                    @else
                                        <span class="badge badge-warning">Non-Driver</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-sm">Duties ({{ $duties->count() }})</h2>
                    <table class="relative min-w-full divide-y divide-gray-300">
                        <thead>
                        <tr>
                            <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-0">Name</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Start</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">End</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        @foreach($duties as $duty)
                        <tr>
                            <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-0"><a href="{{ route('duties.show', $duty) }}">{{ $duty->name }}</a></td>
                            <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $duty->start_time->format('j M Y, H:i') }}</td>
                            <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $duty->end_time->format('j M Y, H:i') }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $duties->links() }}
                    </div>
                </div>
            </div>
        </section>

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
