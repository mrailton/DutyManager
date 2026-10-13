<?php

declare(strict_types=1);

namespace App\Http\Controllers\Members;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ExportMembersController extends Controller
{
    public function __invoke(Request $request): void
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $members = Member::query()
            ->withCount([
                'duties as duties_count' => function ($query) use ($startDate, $endDate): void {
                    $query->whereBetween('start_time', [$startDate, $endDate]);
                },
            ])
            ->orderByDesc('duties_count')
            ->get();

        $writer = SimpleExcelWriter::streamDownload('members_export_' . now()->format('Y-m-d_H-i-s') . '.csv');

        $members->each(function (Member $member) use ($writer): void {
            $writer->addRow([
                'Name' => $member->name,
                'Clinical Level' => $member->clinical_level->label(),
                'Duties Count' => $member->duties_count,
            ]);
        });

        $writer->toBrowser();
    }
}
