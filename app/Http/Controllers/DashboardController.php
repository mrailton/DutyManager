<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ClinicalLevel;
use App\Models\Duty;
use App\Models\Member;
use App\Models\Vehicle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $now = now();
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))
            : $now->copy()->startOfYear();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))
            : $now->copy()->endOfYear();

        if ($endDate->isBefore($startDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();
        $completedRangeEnd = $end->copy()->gt($now) ? $now->copy() : $end->copy();
        $upcomingWindowEnd = $now->copy()->addDays(30);

        $dutiesInRange = Duty::query()
            ->whereBetween('start_time', [$start, $end])
            ->select(['id', 'name', 'start_time', 'end_time', 'covered'])
            ->withCount('members')
            ->with(['members:id,clinical_level'])
            ->get();

        $currentSummary = $this->buildSummaryFromDuties($dutiesInRange, $completedRangeEnd);
        $totalDuties = $currentSummary['total_duties'];
        $completedDuties = $dutiesInRange->where('end_time', '<', $now)->count();
        $upcomingDuties = $totalDuties - $completedDuties;
        $totalVolunteerHours = $currentSummary['total_volunteer_hours'];
        $averageMembersPerDuty = $currentSummary['average_members_per_duty'];

        $totalMembers = Member::count();
        $totalMemberAssignments = $dutiesInRange->sum('members_count');

        $averageDutiesPerMember = $totalMembers > 0
            ? round($totalMemberAssignments / $totalMembers)
            : 0;

        $totalVehicles = Vehicle::count();

        $busiestVehicle = Vehicle::select(['vehicles.id', 'vehicles.callsign', 'vehicles.name'])
            ->selectRaw('COUNT(duty_vehicles.duty_id) as duties_count')
            ->leftJoin('duty_vehicles', 'vehicles.id', '=', 'duty_vehicles.vehicle_id')
            ->leftJoin('duties', 'duty_vehicles.duty_id', '=', 'duties.id')
            ->whereBetween('duties.start_time', [$start, $end])
            ->groupBy('vehicles.id')
            ->orderByDesc('duties_count')
            ->first();

        $busiestMembers = Member::select(['members.id', 'members.name'])
            ->selectRaw('COUNT(duty_members.duty_id) as duties_count')
            ->leftJoin('duty_members', 'members.id', '=', 'duty_members.member_id')
            ->leftJoin('duties', 'duty_members.duty_id', '=', 'duties.id')
            ->whereBetween('duties.start_time', [$start, $end])
            ->where('duties.end_time', '<=', $completedRangeEnd)
            ->groupBy('members.id')
            ->orderByDesc('duties_count')
            ->limit(5)
            ->get();

        $busiestMembers->load(['duties' => function ($query) use ($start, $end, $completedRangeEnd): void {
            $query
                ->whereBetween('start_time', [$start, $end])
                ->where('end_time', '<=', $completedRangeEnd)
                ->select(['duties.id', 'start_time', 'end_time']);
        }]);


        $busiestMembers->each(function (Member $member): void {
            $assignedMinutes = $member->duties->sum(
                fn (Duty $duty): float => $duty->start_time->diffInMinutes($duty->end_time, true)
            );

            $hours = floor($assignedMinutes / 60);
            $minutes = $assignedMinutes % 60;

            $formatted = collect([
                $hours > 0 ? "{$hours} hour" . (1 !== $hours ? 's' : '') : null,
                $minutes > 0 ? "{$minutes} min" . (1 !== $minutes ? 's' : '') : null,
            ])->filter()->implode(' ');


            $member->setAttribute('assigned_hours', $formatted);
        });


        $upcomingDutiesInNext30Days = Duty::query()
            ->whereBetween('start_time', [$now, $upcomingWindowEnd])
            ->orderBy('start_time')
            ->get(['id', 'name', 'start_time', 'covered', 'confirmed']);
        $upcomingUncoveredDuties = $upcomingDutiesInNext30Days
            ->where('covered', false)
            ->values();

        $completedDutiesInRange = $dutiesInRange->filter(
            fn (Duty $duty): bool => $duty->end_time->lte($completedRangeEnd)
        )->values();
        $assignedHoursByClinicalLevel = $this->calculateAssignedHoursByClinicalLevel($completedDutiesInRange);
        $durationInsights = $this->calculateDurationInsights($completedDutiesInRange);

        $busiestMonth = null;

        if ([] !== $currentSummary['duties_by_month']) {
            $busiestMonthKey = collect($currentSummary['duties_by_month'])->sortDesc()->keys()->first();
            $busiestMonth = Carbon::createFromFormat('Y-m-d', $busiestMonthKey . '-01');
        }

        return view('dashboard', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalDuties' => $totalDuties,
            'completedDuties' => $completedDuties,
            'upcomingDuties' => $upcomingDuties,
            'totalVolunteerHours' => $totalVolunteerHours,
            'averageMembersPerDuty' => $averageMembersPerDuty,
            'averageDutiesPerMember' => $averageDutiesPerMember,
            'totalMembers' => $totalMembers,
            'totalVehicles' => $totalVehicles,
            'busiestVehicle' => $busiestVehicle,
            'busiestMembers' => $busiestMembers,
            'busiestMonth' => $busiestMonth,
            'upcomingUncoveredDuties' => $upcomingUncoveredDuties,
            'upcomingDutiesInNext30Days' => $upcomingDutiesInNext30Days,
            'assignedHoursByClinicalLevel' => $assignedHoursByClinicalLevel,
            'durationInsights' => $durationInsights,
        ]);
    }

    /**
     * @return array{
     *   total_duties:int,
     *   total_volunteer_hours:int,
     *   average_members_per_duty:float|int,
     *   duties_by_month:array<string,int>
     * }
     */
    private function buildSummaryFromDuties(Collection $duties, Carbon $completionCutoff): array
    {
        $totalCompletedVolunteerHours = 0;
        $totalMembersAcrossDuties = 0;
        $dutiesByMonth = [];

        /** @var Duty $duty */
        foreach ($duties as $duty) {
            $durationHours = $duty->start_time->diffInMinutes($duty->end_time, true) / 60;
            $totalMembersAcrossDuties += $duty->members_count;

            if ($duty->end_time->lte($completionCutoff)) {
                $totalCompletedVolunteerHours += (int) round($duty->members_count * $durationHours);
            }

            $month = $duty->start_time->format('Y-m');
            $dutiesByMonth[$month] = ($dutiesByMonth[$month] ?? 0) + 1;
        }

        $totalDuties = $duties->count();

        return [
            'total_duties' => $totalDuties,
            'total_volunteer_hours' => $totalCompletedVolunteerHours,
            'average_members_per_duty' => $totalDuties > 0 ? round($totalMembersAcrossDuties / $totalDuties) : 0,
            'duties_by_month' => $dutiesByMonth,
        ];
    }

    /**
     * @return array<int,array{level:string,hours:int}>
     */
    private function calculateAssignedHoursByClinicalLevel(Collection $duties): array
    {
        $assignedHours = collect(ClinicalLevel::cases())
            ->mapWithKeys(fn (ClinicalLevel $level): array => [$level->value => 0.0])
            ->all();

        /** @var Duty $duty */
        foreach ($duties as $duty) {
            $durationHours = $duty->start_time->diffInMinutes($duty->end_time, true) / 60;

            foreach ($duty->members as $member) {
                $level = $member->clinical_level->value;
                $assignedHours[$level] += $durationHours;
            }
        }

        return collect($assignedHours)
            ->map(fn (float $hours, string $level): array => [
                'level' => ClinicalLevel::from($level)->label(),
                'hours' => (int) round($hours),
            ])
            ->filter(fn (array $metric): bool => $metric['hours'] > 0)
            ->sortByDesc('hours')
            ->values()
            ->all();
    }

    /**
     * @return array{
     *   average_hours:float,
     *   average_label:string,
     *   longest:?array{name:string,hours:float,duration_label:string},
     *   shortest:?array{name:string,hours:float,duration_label:string}
     * }
     */
    private function calculateDurationInsights(Collection $duties): array
    {
        if ($duties->isEmpty()) {
            return [
                'average_hours' => 0.0,
                'average_label' => '0h 0m',
                'longest' => null,
                'shortest' => null,
            ];
        }

        $withDurations = $duties->map(fn (Duty $duty): array => [
            'name' => $duty->name,
            'hours' => round($duty->start_time->diffInMinutes($duty->end_time, true) / 60, 1),
        ]);

        $averageHours = round($withDurations->avg('hours'), 1);
        $longest = $withDurations->sortByDesc('hours')->first();
        $shortest = $withDurations->sortBy('hours')->first();

        if (null !== $longest) {
            $longest['duration_label'] = $this->formatHoursMinutes((float) $longest['hours']);
        }

        if (null !== $shortest) {
            $shortest['duration_label'] = $this->formatHoursMinutes((float) $shortest['hours']);
        }

        return [
            'average_hours' => $averageHours,
            'average_label' => $this->formatHoursMinutes($averageHours),
            'longest' => $longest,
            'shortest' => $shortest,
        ];
    }

    private function formatHoursMinutes(float $hours): string
    {
        $totalMinutes = (int) round($hours * 60);
        $wholeHours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return "{$wholeHours}h {$minutes}m";
    }

}
