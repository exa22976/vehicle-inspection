<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InspectionPattern;
use App\Models\InspectionRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 週のナビゲーション
        $weekQuery = $request->query('week');
        $weekStartDate = $weekQuery ? Carbon::parse($weekQuery) : Carbon::now()->startOfWeek(Carbon::MONDAY);

        // 対象週の点検依頼を取得（関連データも一緒に）
        $inspectionRequest = InspectionRequest::with(['records.vehicle', 'records.user', 'pattern'])
            ->where('target_week_start', $weekStartDate->format('Y-m-d'))
            ->first();

        $patterns = InspectionPattern::with('items')->get();

        // 週ナビゲーション用の日付
        $prevWeek = $weekStartDate->copy()->subWeek()->format('Y-m-d');
        $nextWeek = $weekStartDate->copy()->addWeek()->format('Y-m-d');

        // ダッシュボード用の統計情報
        $stats = [
            'total' => 0,
            'completed' => 0,
            'progress_rate' => 0.0,
            'results' => ['正常' => 0, '要確認' => 0, '異常' => 0, '未点検' => 0],
        ];

        // ★★★★★ ここから修正 ★★★★★
        if ($inspectionRequest) {
            $latestRecords = $inspectionRequest->records->where('is_latest', true);

            // 並べ替え処理
            $sortColumn = $request->get('sort', 'vehicle.model_name');
            $sortDirection = $request->get('direction', 'asc');

            // 関連テーブルのカラムでソートするための処理
            $sortedRecords = $latestRecords->sortBy(function ($record) use ($sortColumn) {
                // 'vehicle.model_name' のようなドット記法を解決
                $relations = explode('.', $sortColumn);
                $value = $record;
                foreach ($relations as $relation) {
                    $value = optional($value)->{$relation};
                }
                return $value;
            }, SORT_REGULAR, $sortDirection === 'desc');


            // 統計情報の計算
            $stats['total'] = $latestRecords->count();
            $stats['completed'] = $latestRecords->whereNotIn('status', ['依頼中', '再依頼'])->count();
            if ($stats['total'] > 0) {
                $stats['progress_rate'] = round(($stats['completed'] / $stats['total']) * 100, 1);
            }
            $stats['results']['正常'] = $latestRecords->where('result', '正常')->count();
            $stats['results']['要確認'] = $latestRecords->where('result', '要確認')->count();
            $stats['results']['異常'] = $latestRecords->where('result', '異常')->count();
            $stats['results']['未点検'] = $stats['total'] - $stats['completed'];
        } else {
            $sortedRecords = new Collection();
        }

        return view('admin.dashboard.index', compact('weekStartDate', 'inspectionRequest', 'patterns', 'prevWeek', 'nextWeek', 'stats', 'sortedRecords'));
        // ★★★★★ ここまで修正 ★★★★★
    }
}
