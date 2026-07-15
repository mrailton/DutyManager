<x-layout.app>
    <div x-data="{ editItem: { id: {{ $vehicle->id }}, callsign: '{{ $vehicle->callsign }}', name: '{{ $vehicle->name }}', role: '{{ $vehicle->role->value }}' } }">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('vehicles.index') }}" class="text-sm text-base-content/60 hover:text-base-content">&larr; Back to Vehicles</a>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white mt-1">{{ $vehicle->callsign }}</h1>
            </div>
            <button @click="$refs.editModal.showModal()" class="btn btn-outline btn-primary">Edit</button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title">Details</h2>
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm text-base-content/60">Name</dt>
                                <dd class="font-medium">{{ $vehicle->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-base-content/60">Role</dt>
                                <dd>
                                    <span class="badge {{ $vehicle->role->badgeClass() }}">{{ $vehicle->role->label() }}</span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-sm">Duties ({{ $vehicle->duties->count() }})</h2>
                        <ul class="space-y-2">
                            @forelse($vehicle->duties as $duty)
                                <li>
                                    <a href="{{ route('duties.show', $duty) }}" class="link text-sm font-medium">{{ $duty->name }}</a>
                                    <div class="text-xs text-base-content/60">{{ $duty->start_time->format('j M Y, H:i') }} &mdash; {{ $duty->end_time->format('j M Y, H:i') }}</div>
                                </li>
                            @empty
                                <li class="text-sm text-base-content/60">Not assigned to any duties.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <x-modal name="editModal" title="Edit Vehicle">
            <form :action="`/vehicles/${editItem.id}`" method="POST" class="mt-4">
                @csrf
                @method('PUT')
                <label class="fieldset-label">Callsign</label>
                <input type="text" name="callsign" required class="input w-full" x-model="editItem.callsign" />

                <label class="fieldset-label mt-4">Name</label>
                <input type="text" name="name" required class="input w-full" x-model="editItem.name" />

                <label class="fieldset-label mt-4">Role</label>
                <select name="role" required class="select w-full" x-model="editItem.role">
                    <option value="" disabled>Select role</option>
                    @foreach (App\Enums\VehicleRole::cases() as $role)
                        <option value="{{ $role->value }}">{{ $role->label() }}</option>
                    @endforeach
                </select>

                <div class="modal-action">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </x-modal>
    </div>
</x-layout.app>
