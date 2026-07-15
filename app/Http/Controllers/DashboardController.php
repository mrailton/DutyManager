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
use Illuminate\Support\Facades\DB;

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
        $totalMemberAssignments = DB::table('duty_members')
            ->join('duties', 'duty_members.duty_id', '=', 'duties.id')
            ->whereBetween('duties.start_time', [$start, $end])
            ->count();

        $averageDutiesPerMember = $totalMembers > 0
            ? round($totalMemberAssignments / $totalMembers)
            : 0;

        $totalVehicles = Vehicle::count();

        $busiestVehicle = Vehicle::select('vehicles.*')
            ->selectRaw('COUNT(duty_vehicles.duty_id) as duties_count')
            ->leftJoin('duty_vehicles', 'vehicles.id', '=', 'duty_vehicles.vehicle_id')
            ->leftJoin('duties', 'duty_vehicles.duty_id', '=', 'duties.id')
            ->whereBetween('duties.start_time', [$start, $end])
            ->groupBy('vehicles.id')
            ->orderByDesc('duties_count')
            ->first();

        $busiestMembers = Member::select('members.*')
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
            $assignedHours = $member->duties->sum(
                fn (Duty $duty): float => $duty->start_time->diffInMinutes($duty->end_time, true) / 60
            );
            $member->setAttribute('assigned_hours', round($assignedHours, 1));
        });

        $upcomingUncoveredDuties = Duty::query()
            ->where('covered', false)
            ->whereBetween('start_time', [$now, $now->copy()->addDays(30)])
            ->orderBy('start_time')
            ->get(['id', 'name', 'start_time']);
        $uncoveredUpcomingDuties = $upcomingUncoveredDuties->count();

        $completedDutiesInRange = $dutiesInRange->filter(
            fn (Duty $duty): bool => $duty->end_time->lte($completedRangeEnd)
        )->values();
        $assignedHoursByClinicalLevel = $this->calculateAssignedHoursByClinicalLevel($completedDutiesInRange);
        $durationInsights = $this->calculateDurationInsights($completedDutiesInRange);

        $periodDurationSeconds = max(1, $start->diffInSeconds($end));
        $previousEnd = $start->copy()->subSecond();
        $previousStart = $previousEnd->copy()->subSeconds($periodDurationSeconds);
        $previousSummary = $this->buildSummaryForRange($previousStart, $previousEnd, $now);

        $periodChanges = [
            'duties' => $this->calculatePercentChange(
                $currentSummary['total_duties'],
                $previousSummary['total_duties']
            ),
            'volunteer_hours' => $this->calculatePercentChange(
                $currentSummary['total_volunteer_hours'],
                $previousSummary['total_volunteer_hours']
            ),
            'average_members_per_duty' => $this->calculatePercentChange(
                $currentSummary['average_members_per_duty'],
                $previousSummary['average_members_per_duty']
            ),
        ];

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
            'uncoveredUpcomingDuties' => $uncoveredUpcomingDuties,
            'upcomingUncoveredDuties' => $upcomingUncoveredDuties,
            'assignedHoursByClinicalLevel' => $assignedHoursByClinicalLevel,
            'durationInsights' => $durationInsights,
            'periodChanges' => $periodChanges,
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
     * @return array{
     *   total_duties:int,
     *   total_volunteer_hours:int,
     *   average_members_per_duty:float|int
     * }
     */
    private function buildSummaryForRange(Carbon $start, Carbon $end, Carbon $completionCutoff): array
    {
        $totalCompletedVolunteerHours = 0;
        $totalMembersAcrossDuties = 0;
        $totalDuties = 0;

        foreach (
            Duty::query()
                ->whereBetween('start_time', [$start, $end])
                ->select(['id', 'start_time', 'end_time', 'covered'])
                ->withCount('members')
                ->cursor() as $duty
        ) {
            ++$totalDuties;
            $durationHours = $duty->start_time->diffInMinutes($duty->end_time, true) / 60;
            $totalMembersAcrossDuties += $duty->members_count;

            if ($duty->end_time->lte($completionCutoff)) {
                $totalCompletedVolunteerHours += (int) round($duty->members_count * $durationHours);
            }
        }

        return [
            'total_duties' => $totalDuties,
            'total_volunteer_hours' => $totalCompletedVolunteerHours,
            'average_members_per_duty' => $totalDuties > 0 ? round($totalMembersAcrossDuties / $totalDuties) : 0,
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

    private function calculatePercentChange(int|float $current, int|float $previous): float
    {
        if (0.0 === (float) $previous) {
            return 0.0 === (float) $current ? 0.0 : 100.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
