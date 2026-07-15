<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Duty;
use App\Models\Member;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = $request->string('q')->trim();

        if ($q->isEmpty()) {
            if ($request->header('HX-Request')) {
                return response('');
            }

            return redirect()->route('dashboard');
        }

        $query = $q->value();

        $duties = Duty::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('organiser', 'like', "%{$query}%")
            ->orderBy('start_time', 'desc')
            ->limit(5)
            ->get();

        $members = Member::query()
            ->where('name', 'like', "%{$query}%")
            ->orderBy('name')
            ->limit(5)
            ->get();

        $vehicles = Vehicle::query()
            ->where('callsign', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%")
            ->orderBy('callsign')
            ->limit(5)
            ->get();

        $hasResults = $duties->isNotEmpty() || $members->isNotEmpty() || $vehicles->isNotEmpty();

        if ($request->header('HX-Request')) {
            return response()->view('components.search-results', [
                'duties' => $duties,
                'members' => $members,
                'vehicles' => $vehicles,
                'hasResults' => $hasResults,
                'query' => $query,
            ]);
        }

        return redirect()->route('dashboard');
    }
}
