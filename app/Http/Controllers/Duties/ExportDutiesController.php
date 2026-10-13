<?php

declare(strict_types=1);

namespace App\Http\Controllers\Duties;

use App\Http\Controllers\Controller;
use App\Models\Duty;
use Illuminate\Http\Request;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ExportDutiesController extends Controller
{
    public function __invoke(Request $request): void
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $duties = Duty::query()
            ->when($startDate, fn ($query) => $query->whereDate('start_time', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->whereDate('end_time', '<=', $endDate))
            ->orderByDesc('start_time')
            ->get();

        $writer = SimpleExcelWriter::streamDownload('duties_export_' . now()->format('Y-m-d_H-i-s') . '.csv');

        $duties->map(function (Duty $duty) use ($writer): void {
            $writer->addRow([
                'Name' => $duty->name,
                'Organiser' => $duty->organiser,
                'Start Time' => $duty->start_time->format('Y-m-d H:i'),
                'End Time' => $duty->end_time->format('Y-m-d H:i'),
                'Duration (hours)' => $duty->start_time->diffInHours($duty->end_time),
                'Number Vehicles' => $duty->vehicles->count(),
                'Number Members' => $duty->members->count(),
            ]);
        });

        $writer->toBrowser();
    }
}
