<div class="rounded-lg bg-white shadow-lg ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
    @unless ($hasResults)
        <div class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
            No results found for <strong>{{ $query }}</strong>.
        </div>
    @else
        <div class="max-h-96 overflow-y-auto py-2">
            @if ($duties->isNotEmpty())
                <div class="px-4 py-1 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Duties</div>
                @foreach ($duties as $duty)
                    <a href="{{ route('duties.show', $duty) }}"
                       class="flex items-center gap-3 px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-white/5">
                        <span class="font-medium text-gray-900 dark:text-white">{{ $duty->name }}</span>
                        <span class="text-gray-500 dark:text-gray-400">{{ $duty->organiser }}</span>
                        <span class="ml-auto text-xs text-gray-400 dark:text-gray-500">{{ $duty->start_time->format('j M') }}</span>
                    </a>
                @endforeach
            @endif

            @if ($members->isNotEmpty())
                <div class="mt-1 border-t border-gray-100 px-4 py-1 pt-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:border-white/10 dark:text-gray-500">Members</div>
                @foreach ($members as $member)
                    <a href="{{ route('members.show', $member) }}"
                       class="flex items-center gap-3 px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-white/5">
                        <span class="font-medium text-gray-900 dark:text-white">{{ $member->name }}</span>
                    </a>
                @endforeach
            @endif

            @if ($vehicles->isNotEmpty())
                <div class="mt-1 border-t border-gray-100 px-4 py-1 pt-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:border-white/10 dark:text-gray-500">Vehicles</div>
                @foreach ($vehicles as $vehicle)
                    <a href="{{ route('vehicles.show', $vehicle) }}"
                       class="flex items-center gap-3 px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-white/5">
                        <span class="font-mono font-medium text-gray-900 dark:text-white">{{ $vehicle->callsign }}</span>
                        <span class="text-gray-500 dark:text-gray-400">{{ $vehicle->name }}</span>
                    </a>
                @endforeach
            @endif
        </div>
    @endunless
</div>
