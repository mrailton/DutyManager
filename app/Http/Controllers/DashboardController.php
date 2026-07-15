<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Duty;
use App\Models\Member;
use App\Models\Vehicle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            : $now;

        if ($endDate->isBefore($startDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        $dutiesInRangeQuery = Duty::query()
            ->whereBetween('start_time', [$start, $end]);

        $totalDuties = (clone $dutiesInRangeQuery)->count();

        $totalVolunteerHours = 0;
        $totalMembersAcrossDuties = 0;
        $dutiesByMonth = [];

        foreach ((clone $dutiesInRangeQuery)->select(['id', 'start_time', 'end_time'])->withCount('members')->cursor() as $duty) {
            $totalMembersAcrossDuties += $duty->members_count;
            $totalVolunteerHours += (int) round(
                $duty->members_count * $duty->start_time->diffInHours($duty->end_time, true)
            );

            $month = $duty->start_time->format('Y-m');
            $dutiesByMonth[$month] = ($dutiesByMonth[$month] ?? 0) + 1;
        }

        $averageMembersPerDuty = $totalDuties > 0 ? round($totalMembersAcrossDuties / $totalDuties) : 0;

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
            ->groupBy('members.id')
            ->orderByDesc('duties_count')
            ->limit(5)
            ->get();

        $busiestMonth = null;

        if ([] !== $dutiesByMonth) {
            $busiestMonth = Carbon::createFromFormat('Y-m-d', collect($dutiesByMonth)->sortDesc()->keys()->first() . '-01');
        }

        return view('dashboard', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalDuties' => $totalDuties,
            'totalVolunteerHours' => $totalVolunteerHours,
            'averageMembersPerDuty' => $averageMembersPerDuty,
            'averageDutiesPerMember' => $averageDutiesPerMember,
            'totalMembers' => $totalMembers,
            'totalVehicles' => $totalVehicles,
            'busiestVehicle' => $busiestVehicle,
            'busiestMembers' => $busiestMembers,
            'busiestMonth' => $busiestMonth,
        ]);
    }
}
