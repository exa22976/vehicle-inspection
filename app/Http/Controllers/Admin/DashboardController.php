<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InspectionRecord; // InspectionRecord を use
use App\Models\InspectionRequest; // InspectionRequest を use
use App\Models\InspectionPattern; // InspectionPattern を use
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $weekStartDate = Carbon::parse($request->query('week', Carbon::today()->startOfWeek(Carbon::MONDAY)))->startOfDay();
        $prevWeek = $weekStartDate->copy()->subWeek()->format('Y-m-d');
        $nextWeek = $weekStartDate->copy()->addWeek()->format('Y-m-d');

        // --- 絞り込み条件の取得 ---
        $filterStatus = $request->query('status');
        // ★★★ 結果フィルターの値を取得 ★★★
        $filterResult = $request->query('result');
        // ★★★ ここまで ★★★

        // その週の点検リクエストを取得 (関連情報も Eager Load)
        $inspectionRequest = InspectionRequest::with([
            'records' => function ($query) {
                // Eager Load 時にも関連情報を読み込む
                $query->with(['vehicle.users', 'user']);
            },
            'pattern'
        ])
            ->where('target_week_start', $weekStartDate->format('Y-m-d'))
            ->first();

        // 点検パターン (すべての項目を含む) を取得 - 変更なし
        $patterns = InspectionPattern::with('items')->get();

        // 初期化 - 変更なし
        $stats = [
            'total' => 0,
            'completed' => 0,
            'progress_rate' => 0,
            'results' => ['正常' => 0, '要確認' => 0, '異常' => 0],
        ];
        $sortedRecords = collect(); // 初期は空のコレクション

        $statuses = ['依頼中', '再依頼', '点検済み']; // フィルター用
        $resultOptions = ['正常', '要確認', '異常']; // フィルター用
        $sortColumn = $request->query('sort', 'vehicle.model_name'); // デフォルトソート
        $sortDirection = $request->query('direction', 'asc');

        if ($inspectionRequest) {
            // 集計は絞り込み *前* の全レコードで行う
            $allRecordsForStats = $inspectionRequest->records()->get(); // 関連レコードを全て取得

            $stats['total'] = $allRecordsForStats->count();
            $stats['completed'] = $allRecordsForStats->where('status', '点検済み')->count();
            if ($stats['total'] > 0) {
                $stats['progress_rate'] = round(($stats['completed'] / $stats['total']) * 100);
            }
            $stats['results']['正常'] = $allRecordsForStats->where('result', '正常')->count();
            $stats['results']['要確認'] = $allRecordsForStats->where('result', '要確認')->count();
            $stats['results']['異常'] = $allRecordsForStats->where('result', '異常')->count();

            // --- 表示用レコードの絞り込み ---
            $recordsToDisplay = $allRecordsForStats; // 絞り込み前の全レコードから開始

            // ステータスでの絞り込み
            if ($filterStatus) {
                $recordsToDisplay = $recordsToDisplay->where('status', $filterStatus);
            }
            if ($filterResult) {
                $recordsToDisplay = $recordsToDisplay->where('result', $filterResult);
            }

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

        // ビューに変数を渡す
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
            'filterStatus',
            'resultOptions',
            'filterResult'
        ));
    }
}
