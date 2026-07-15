<x-layout.app>
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('duties.index') }}" class="text-sm text-base-content/60 hover:text-base-content">&larr; Back to Duties</a>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white mt-1">{{ $duty->name }}</h1>
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
                            <dt class="text-sm text-base-content/60">Organiser</dt>
                            <dd class="font-medium">{{ $duty->organiser }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-base-content/60">Covered</dt>
                            <dd>
                                @if ($duty->covered)
                                    <span class="badge badge-soft badge-success">Yes</span>
                                @else
                                    <span class="badge badge-soft">No</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-base-content/60">Start Time</dt>
                            <dd class="font-medium">{{ $duty->start_time->format('l, j F Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-base-content/60">End Time</dt>
                            <dd class="font-medium">{{ $duty->end_time->format('l, j F Y H:i') }}</dd>
                        </div>
                    </dl>

                    @if ($duty->notes)
                        <div class="mt-4">
                            <dt class="text-sm text-base-content/60">Notes</dt>
                            <dd class="mt-1 whitespace-pre-wrap text-sm">{{ $duty->notes }}</dd>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-sm">Members ({{ $duty->members->count() }})</h2>
                    <ul class="space-y-2">
                        @forelse($duty->members as $member)
                            <li class="flex items-center gap-2">
                                <div>
                                    <div class="text-sm font-medium">{{ $member->name }}</div>
                                    <div class="text-xs text-base-content/60">
                                        <span class="badge {{ $member->clinical_level->badgeClass() }} badge-xs">{{ $member->clinical_level->label() }}</span>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-sm text-base-content/60">No members assigned.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-sm">Vehicles ({{ $duty->vehicles->count() }})</h2>
                    <ul class="space-y-2">
                        @forelse($duty->vehicles as $vehicle)
                            <li class="flex items-center gap-2">
                                <span class="badge badge-ghost badge-sm font-mono">{{ $vehicle->callsign }}</span>
                                <span class="text-sm">{{ $vehicle->name }}</span>
                            </li>
                        @empty
                            <li class="text-sm text-base-content/60">No vehicles assigned.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <x-modal name="editModal" title="Edit Duty" width="max-w-2xl">
        <form action="{{ route('duties.update', $duty) }}" method="POST" class="mt-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="fieldset-label">Name</label>
                    <input type="text" name="name" value="{{ $duty->name }}" required class="input w-full" />
                </div>
                <div>
                    <label class="fieldset-label">Organiser</label>
                    <input type="text" name="organiser" value="{{ $duty->organiser }}" required class="input w-full" />
                </div>
            </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="fieldset-label">Start Date</label>
                        <input type="date" name="start_date" value="{{ $duty->start_time->format('Y-m-d') }}" required class="input w-full" />
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <select name="start_hour" required class="select w-full">
                                @for ($h = 0; $h <= 23; $h++)
                                    <option value="{{ sprintf('%02d', $h) }}" @selected($duty->start_time->format('H') === sprintf('%02d', $h))>{{ sprintf('%02d', $h) }}</option>
                                @endfor
                            </select>
                            <select name="start_minute" required class="select w-full">
                                <option value="00" @selected($duty->start_time->format('i') === '00')>00</option>
                                <option value="15" @selected($duty->start_time->format('i') === '15')>15</option>
                                <option value="30" @selected($duty->start_time->format('i') === '30')>30</option>
                                <option value="45" @selected($duty->start_time->format('i') === '45')>45</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="fieldset-label">End Date</label>
                        <input type="date" name="end_date" value="{{ $duty->end_time->format('Y-m-d') }}" required class="input w-full" />
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <select name="end_hour" required class="select w-full">
                                @for ($h = 0; $h <= 23; $h++)
                                    <option value="{{ sprintf('%02d', $h) }}" @selected($duty->end_time->format('H') === sprintf('%02d', $h))>{{ sprintf('%02d', $h) }}</option>
                                @endfor
                            </select>
                            <select name="end_minute" required class="select w-full">
                                <option value="00" @selected($duty->end_time->format('i') === '00')>00</option>
                                <option value="15" @selected($duty->end_time->format('i') === '15')>15</option>
                                <option value="30" @selected($duty->end_time->format('i') === '30')>30</option>
                                <option value="45" @selected($duty->end_time->format('i') === '45')>45</option>
                            </select>
                        </div>
                    </div>
                </div>

            <div class="mt-4 flex items-center gap-2">
                <input type="checkbox" name="covered" value="1" class="checkbox" id="edit-duty-covered" @checked($duty->covered) />
                <label for="edit-duty-covered" class="fieldset-label">Covered</label>
            </div>

            <div class="mt-4">
                <label class="fieldset-label">Notes</label>
                <textarea name="notes" class="textarea w-full" rows="3">{{ $duty->notes }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="fieldset-label">Members</label>
                    <div class="mt-1 max-h-48 overflow-y-auto space-y-1 rounded-md border border-base-300 p-2">
                        @foreach ($members as $member)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="member_ids[]" value="{{ $member->id }}" class="checkbox checkbox-sm" @checked($duty->members->contains($member)) />
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
                                <input type="checkbox" name="vehicle_ids[]" value="{{ $vehicle->id }}" class="checkbox checkbox-sm" @checked($duty->vehicles->contains($vehicle)) />
                                <span class="text-sm">{{ $vehicle->callsign }} — {{ $vehicle->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="modal-action">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </x-modal>
</x-layout.app>
