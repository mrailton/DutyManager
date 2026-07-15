<x-layout.app>
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Duties</h1>
        <button @click="$refs.createModal.showModal()" class="btn btn-primary">Add Duty</button>
    </div>

    <div class="card bg-base-100 mt-6 shadow-sm">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Organiser</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Covered</th>
                            <th class="w-20"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($duties as $duty)
                            <tr>
                                <td>
                                    <a href="{{ route('duties.show', $duty) }}" class="font-medium hover:link">{{ $duty->name }}</a>
                                </td>
                                <td>{{ $duty->organiser }}</td>
                                <td>{{ $duty->start_time->format('j M Y, H:i') }}</td>
                                <td>{{ $duty->end_time->format('j M Y, H:i') }}</td>
                                <td>
                                    @if ($duty->covered)
                                        <span class="badge badge-soft badge-success">Yes</span>
                                    @else
                                        <span class="badge badge-soft">No</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('duties.show', $duty) }}" class="btn btn-ghost btn-sm">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-base-content/60">
                                    No duties found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($duties->hasPages())
                <div class="border-t border-base-200 px-4 py-3">
                    {{ $duties->links() }}
                </div>
            @endif
        </div>
    </div>

    <x-modal name="createModal" title="Add Duty" width="max-w-2xl">
        <form action="{{ route('duties.store') }}" method="POST" class="mt-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="fieldset-label">Name</label>
                    <input type="text" name="name" required class="input w-full" placeholder="Duty name" />
                </div>
                <div>
                    <label class="fieldset-label">Organiser</label>
                    <input type="text" name="organiser" required class="input w-full" placeholder="Organiser name" />
                </div>
            </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="fieldset-label">Start Date</label>
                        <input type="date" name="start_date" required class="input w-full" />
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <select name="start_hour" required class="select w-full">
                                @for ($h = 0; $h <= 23; $h++)
                                    <option value="{{ sprintf('%02d', $h) }}">{{ sprintf('%02d', $h) }}</option>
                                @endfor
                            </select>
                            <select name="start_minute" required class="select w-full">
                                <option value="00">00</option>
                                <option value="15">15</option>
                                <option value="30">30</option>
                                <option value="45">45</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="fieldset-label">End Date</label>
                        <input type="date" name="end_date" required class="input w-full" />
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <select name="end_hour" required class="select w-full">
                                @for ($h = 0; $h <= 23; $h++)
                                    <option value="{{ sprintf('%02d', $h) }}">{{ sprintf('%02d', $h) }}</option>
                                @endfor
                            </select>
                            <select name="end_minute" required class="select w-full">
                                <option value="00">00</option>
                                <option value="15">15</option>
                                <option value="30">30</option>
                                <option value="45">45</option>
                            </select>
                        </div>
                    </div>
                </div>

            <div class="mt-4 flex items-center gap-2">
                <input type="checkbox" name="covered" value="1" class="checkbox" id="duty-covered" />
                <label for="duty-covered" class="fieldset-label">Covered</label>
            </div>

            <div class="mt-4">
                <label class="fieldset-label">Notes</label>
                <textarea name="notes" class="textarea w-full" placeholder="Optional notes" rows="3"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="fieldset-label">Members</label>
                    <div class="mt-1 max-h-48 overflow-y-auto space-y-1 rounded-md border border-base-300 p-2">
                        @foreach ($members as $member)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="member_ids[]" value="{{ $member->id }}" class="checkbox checkbox-sm" />
                                <span class="text-sm">{{ $member->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="fieldset-label">Vehicles</label>
                    <div class="mt-1 max-h-48 overflow-y-auto space-y-1 rounded-md border border-base-300 p-2">
                        @foreach ($vehicles as $vehicle)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="vehicle_ids[]" value="{{ $vehicle->id }}" class="checkbox checkbox-sm" />
                                <span class="text-sm">{{ $vehicle->callsign }} — {{ $vehicle->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="modal-action">
                <button type="submit" class="btn btn-primary">Create Duty</button>
            </div>
        </form>
    </x-modal>
</x-layout.app>
