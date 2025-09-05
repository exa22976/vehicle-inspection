<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InspectionRequest;
use App\Models\InspectionPattern;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $weekStartDate = Carbon::parse($request->query('week', Carbon::today()->startOfWeek(Carbon::MONDAY)))->startOfDay();
        $prevWeek = $weekStartDate->copy()->subWeek()->format('Y-m-d');
        $nextWeek = $weekStartDate->copy()->addWeek()->format('Y-m-d');

        $filterStatus = $request->query('status');

        $inspectionRequest = InspectionRequest::with(['records.vehicle.users', 'records.user', 'pattern'])
            ->where('target_week_start', $weekStartDate->format('Y-m-d'))
            ->first();

        $patterns = InspectionPattern::with('items')->get();
        $stats = [
            'total' => 0,
            'completed' => 0,
            'progress_rate' => 0,
            'results' => ['正常' => 0, '要確認' => 0, '異常' => 0],
        ];
        $sortedRecords = collect();
        $statuses = ['依頼中', '再依頼', '点検済み'];

        // ★★★★★ ここから修正 ★★★★★
        // 変数が必ず定義されるように、ifブロックの外に移動
        $sortColumn = $request->query('sort', 'vehicle.model_name');
        $sortDirection = $request->query('direction', 'asc');
        // ★★★★★ ここまで修正 ★★★★★

        if ($inspectionRequest) {
            $allRecords = $inspectionRequest->records;
            $stats['total'] = $allRecords->count();
            $stats['completed'] = $allRecords->where('status', '点検済み')->count();
            if ($stats['total'] > 0) {
                $stats['progress_rate'] = round(($stats['completed'] / $stats['total']) * 100);
            }
            $stats['results']['正常'] = $allRecords->where('result', '正常')->count();
            $stats['results']['要確認'] = $allRecords->where('result', '要確認')->count();
            $stats['results']['異常'] = $allRecords->where('result', '異常')->count();

            $recordsToDisplay = $filterStatus
                ? $allRecords->where('status', $filterStatus)
                : $allRecords;

            $sortedRecords = $recordsToDisplay->sortBy(function ($record) use ($sortColumn) {
                switch ($sortColumn) {
                    case 'vehicle.model_name':
                        return optional($record->vehicle)->model_name;
                    case 'user.name':
                        return optional($record->user)->name;
                    case 'inspected_at':
                        return $record->inspected_at;
                    default:
                        return $record->{$sortColumn};
                }
            }, SORT_REGULAR, $sortDirection === 'desc');
        }

        return view('admin.dashboard.index', compact(
            'weekStartDate',
            'prevWeek',
            'nextWeek',
            'inspectionRequest',
            'patterns',
            'stats',
            'sortedRecords',
            'sortColumn',
            'sortDirection',
            'statuses',
            'filterStatus'
        ));
    }
}
