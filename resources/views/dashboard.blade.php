<x-layout.app>
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Dashboard</h1>
    </div>

    <div class="card bg-base-100 shadow-sm mt-6">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="fieldset-label">From</label>
                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="input" />
                </div>
                <div>
                    <label class="fieldset-label">To</label>
                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="input" />
                </div>
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="{{ route('dashboard') }}" class="btn btn-ghost">Reset</a>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
        <div class="stat bg-gradient-to-br from-primary to-primary/80 text-primary-content rounded-box shadow-sm">
            <div class="stat-title text-primary-content/80">Total Duties</div>
            <div class="stat-value">{{ number_format($totalDuties) }}</div>
            <div class="stat-desc text-primary-content/60">{{ $startDate->format('j M') }} &mdash; {{ $endDate->format('j M Y') }}</div>
            <div class="stat-desc text-primary-content/80">Completed: {{ number_format($completedDuties) }} &middot; Upcoming: {{ number_format($upcomingDuties) }}</div>
        </div>

        <div class="stat bg-gradient-to-br from-secondary to-secondary/80 text-secondary-content rounded-box shadow-sm">
            <div class="stat-title text-secondary-content/80">Volunteer Hours</div>
            <div class="stat-value">{{ number_format($totalVolunteerHours) }}</div>
            <div class="stat-desc text-secondary-content/60">Total across all completed duties</div>
        </div>

        <div class="stat bg-gradient-to-br from-accent to-accent/80 text-accent-content rounded-box shadow-sm">
            <div class="stat-title text-accent-content/80">Avg Members / Duty</div>
            <div class="stat-value">{{ $averageMembersPerDuty }}</div>
            <div class="stat-desc text-accent-content/60">Nearest whole number</div>
        </div>

        <div class="stat bg-gradient-to-br from-info to-info/80 text-info-content rounded-box shadow-sm">
            <div class="stat-title text-info-content/80">Avg Duties / Member</div>
            <div class="stat-value">{{ $averageDutiesPerMember }}</div>
            <div class="stat-desc text-info-content/60">Nearest whole number</div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-sm">Active Members</h2>
                <p class="text-3xl font-bold">{{ number_format($totalMembers) }}</p>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-sm">Total Vehicles</h2>
                <p class="text-3xl font-bold">{{ number_format($totalVehicles) }}</p>
            </div>
        </div>

        @if ($busiestMonth)
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-sm">Busiest Month</h2>
                    <p class="text-3xl font-bold">{{ $busiestMonth->format('F Y') }}</p>
                </div>
            </div>
        @endif

        @if ($busiestVehicle)
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-sm">Most Used Vehicle</h2>
                    <p class="text-xl font-bold">{{ $busiestVehicle->callsign }} &mdash; {{ $busiestVehicle->name }} <span class="text-base font-normal text-base-content/60">({{ $busiestVehicle->duties_count }} duties)</span></p>
                </div>
            </div>
        @endif

        @if ($busiestMembers->isNotEmpty())
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-sm">Top 5 Busiest Members</h2>
                    <ol class="list-decimal list-inside space-y-1">
                        @foreach ($busiestMembers as $member)
                            <li class="text-sm">
                                <a href="{{ route('members.show', $member) }}" class="link font-medium">{{ $member->name }}</a>
                                <span class="text-base-content/60">({{ $member->duties_count }} duties, {{ $member->assigned_hours }})</span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        @endif

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-sm">Assigned Hours by Clinical Level</h2>
                @if ($assignedHoursByClinicalLevel === [])
                    <p class="text-sm text-base-content/60">No assigned hours in this period.</p>
                @else
                    <ul class="space-y-2">
                        @foreach ($assignedHoursByClinicalLevel as $metric)
                            <li class="flex items-center justify-between text-sm">
                                <span>{{ $metric['level'] }}</span>
                                <span class="font-medium">{{ number_format($metric['hours']) }}h</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-sm">Uncovered Upcoming Duties (30 Days)</h2>
                <p class="text-3xl font-bold">{{ number_format($upcomingUncoveredDuties->count()) }}</p>

                @if ($upcomingUncoveredDuties->isEmpty())
                    <p class="mt-2 text-sm text-base-content/60">No uncovered upcoming duties.</p>
                @else
                    <ul class="mt-2 space-y-1">
                        @foreach ($upcomingUncoveredDuties as $duty)
                            <li class="text-sm">
                                <a href="{{ route('duties.show', $duty) }}" class="link font-medium">{{ $duty->name }}</a>
                                <span class="text-base-content/60">({{ $duty->start_time->format('j M Y, H:i') }})</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-sm">Upcoming Duties (30 Days)</h2>
                <p class="text-3xl font-bold">{{ number_format($upcomingDutiesInNext30Days->count()) }}</p>

                @if ($upcomingDutiesInNext30Days->isEmpty())
                    <p class="mt-2 text-sm text-base-content/60">No upcoming duties.</p>
                @else
                    <ul class="mt-2 space-y-1">
                        @foreach ($upcomingDutiesInNext30Days as $duty)
                            <li class="text-sm">
                                <a href="{{ route('duties.show', $duty) }}" class="link font-medium">{{ $duty->name }}</a>
                                <span class="text-base-content/60">
                                    ({{ $duty->start_time->format('j M Y, H:i') }} &middot; {{ $duty->covered ? 'Covered' : 'Not Covered' }} &middot; {{ $duty->confirmed ? 'Confirmed' : 'Not Confirmed' }})
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-sm">Duty Duration Insights</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-base-content/60">Average</dt>
                        <dd class="font-medium">{{ $durationInsights['average_label'] }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-base-content/60">Longest</dt>
                        @if ($durationInsights['longest'])
                            <dd class="text-right">
                                <span class="font-medium">{{ $durationInsights['longest']['name'] }}</span>
                                <span class="block text-xs text-base-content/60">{{ $durationInsights['longest']['duration_label'] }}</span>
                            </dd>
                        @else
                            <dd class="text-base-content/60">N/A</dd>
                        @endif
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-base-content/60">Shortest</dt>
                        @if ($durationInsights['shortest'])
                            <dd class="text-right">
                                <span class="font-medium">{{ $durationInsights['shortest']['name'] }}</span>
                                <span class="block text-xs text-base-content/60">{{ $durationInsights['shortest']['duration_label'] }}</span>
                            </dd>
                        @else
                            <dd class="text-base-content/60">N/A</dd>
                        @endif
                    </div>
                </dl>
            </div>
        </div>
    </div>

</x-layout.app>
