<x-layout.app>
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Vehicles</h1>
        <button @click="$refs.createModal.showModal()" class="btn btn-primary">Add Vehicle</button>
    </div>

    <div class="card bg-base-100 mt-6 shadow-sm">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Callsign</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th class="w-20"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehicles as $vehicle)
                            <tr>
                                <td>
                                    <div class="font-mono font-medium">{{ $vehicle->callsign }}</div>
                                </td>
                                <td>{{ $vehicle->name }}</td>
                                <td>
                                    <span class="badge {{ $vehicle->role->badgeClass() }}">{{ $vehicle->role->label() }}</span>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-ghost btn-sm">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-8 text-base-content/60">
                                    No vehicles found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($vehicles->hasPages())
                <div class="border-t border-base-200 px-4 py-3">
                    {{ $vehicles->links() }}
                </div>
            @endif
        </div>
    </div>

    <dialog x-ref="createModal" class="modal">
        <div class="modal-box">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
            <h3 class="text-lg font-bold">Add Vehicle</h3>
            <form action="{{ route('vehicles.store') }}" method="POST" class="mt-4">
                @csrf
                <label class="fieldset-label">Callsign</label>
                <input type="text" name="callsign" required class="input w-full" placeholder="e.g. DUTY-1" />

                <label class="fieldset-label mt-4">Name</label>
                <input type="text" name="name" required class="input w-full" placeholder="Vehicle name" />

                <label class="fieldset-label mt-4">Role</label>
                <select name="role" required class="select w-full">
                    <option value="" disabled selected>Select role</option>
                    @foreach (App\Enums\VehicleRole::cases() as $role)
                        <option value="{{ $role->value }}">{{ $role->label() }}</option>
                    @endforeach
                </select>

                <div class="modal-action">
                    <button type="submit" class="btn btn-primary">Create Vehicle</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</x-layout.app>
