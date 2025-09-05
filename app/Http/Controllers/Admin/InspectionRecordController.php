<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InspectionRecord;
use App\Models\AdminComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InspectionRecordController extends Controller
{
    /**
     * 点検記録の詳細を表示
     */
    public function show(InspectionRecord $inspectionRecord)
    {
        // 関連データを読み込む
        $inspectionRecord->load('vehicle', 'inspectionRequest.pattern', 'details.item', 'adminComments.user');

        // この点検記録と同じ車両・週の過去の履歴を取得
        $historicalRecords = InspectionRecord::where('inspection_request_id', $inspectionRecord->inspection_request_id)
            ->where('vehicle_id', $inspectionRecord->vehicle_id)
            ->where('id', '!=', $inspectionRecord->id) // 現在の記録は除く
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.records.show', compact('inspectionRecord', 'historicalRecords'));
    }


    /**
     * 管理者コメントを投稿
     */
    public function storeComment(Request $request, InspectionRecord $inspectionRecord)
    {
        $request->validate(['comment' => 'required|string']);

        $inspectionRecord->adminComments()->create([
            'user_id' => Auth::id(),
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'コメントを投稿しました。');
    }

    /**
     * 異常ステータスを更新
     */
    public function updateStatus(Request $request, InspectionRecord $inspectionRecord)
    {
        // ★★★★★ ここからメソッド全体を修正 ★★★★★
        $validated = $request->validate([
            'issue_status' => 'required|string|in:未対応,対応済み',
            'resolved_at' => 'nullable|date',
            'admin_comment' => 'nullable|string|max:1000', // 管理者コメントのバリデーションを追加
        ]);

        // 点検記録本体（対応状況と完了日）を更新
        $inspectionRecord->update([
            'issue_status' => $validated['issue_status'],
            'resolved_at' => $validated['resolved_at'],
        ]);

        // 管理者コメントが入力されている場合のみ、admin_commentsテーブルに保存
        if (!empty($validated['admin_comment'])) {
            $inspectionRecord->adminComments()->create([
                'user_id' => Auth::id(), // 現在ログインしている管理者のID
                'comment' => $validated['admin_comment'],
            ]);
        }

        return redirect()->back()->with('success', '対応状況を更新しました。');
        // ★★★★★ ここまでメソッド全体を修正 ★★★★★
    }

    /**
     * 再点検を依頼する
     */
    public function reRequest(Request $request, InspectionRecord $inspectionRecord)
    {
        DB::beginTransaction();
        try {
            // 親の点検依頼を取得
            $inspectionRequest = $inspectionRecord->inspectionRequest;
            if (!$inspectionRequest) {
                throw new \Exception('親の点検依頼が見つかりません。データが不正な状態です。');
            }

            // この車両の最新のレコードをすべて非最新にする
            $inspectionRequest->records()
                ->where('vehicle_id', $inspectionRecord->vehicle_id)
                ->update(['is_latest' => false]);

            // 新しい点検記録を作成
            $newRequestRecord = $inspectionRequest->records()->create([
                'vehicle_id' => $inspectionRecord->vehicle_id,
                'status' => '再依頼', // ステータスを「再依頼」に
                'one_time_token' => Str::random(40),
                'token_expires_at' => Carbon::now()->addDays(7),
                'is_latest' => true, // これが最新のレコード
            ]);

            // TODO: ここに担当者へのメール通知処理を追加する

            DB::commit();

            // ★★★★★ 修正箇所 ★★★★★
            // 変数名を $newRecord から $newRequestRecord に修正
            $message = $newRequestRecord->vehicle->model_name . ' の再点検依頼を作成しました。';
            return redirect()->route('admin.dashboard', ['week' => $inspectionRequest->target_week_start])
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('再点検依頼作成エラー: ' . $e->getMessage());
            return back()->with('error', '再点検依頼の作成中にエラーが発生しました。' . $e->getMessage());
        }
    }
}
