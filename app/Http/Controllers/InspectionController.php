<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InspectionRecord;
use App\Models\InspectionRecordDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InspectionController extends Controller
{
    /**
     * 点検フォームを表示する
     */
    public function showForm(string $token)
    {
        $record = InspectionRecord::where('one_time_token', $token)->first();

        // ★ステータスチェックをより厳密に（依頼中と再依頼のみ許可）
        if (!$record || $record->token_expires_at < now() || !in_array($record->status, ['依頼中', '再依頼'])) {
            return view('inspections.invalid');
        }

        $inspectionRequest = $record->inspectionRequest;
        $vehicle = $record->vehicle;
        $pattern = $inspectionRequest->pattern;

        // ★★★★★ 変更点 ★★★★★
        // 点検項目を、車両のカテゴリと完全に一致するもののみに絞り込む
        $items = $pattern->items()
            ->where('category', $vehicle->category . '共通') // 例: 車両なら「車両共通」、重機なら「重機共通」のみ
            ->orderBy('display_order')
            ->get();
        // ★★★★★ ここまで ★★★★★

        return view('inspections.form', compact('record', 'inspectionRequest', 'vehicle', 'pattern', 'items'));
    }

    /**
     * 点検フォームの報告を処理する
     */
    public function submitForm(Request $request, string $token)
    {
        $record = InspectionRecord::where('one_time_token', $token)->first();

        if (!$record || $record->token_expires_at < now() || !in_array($record->status, ['依頼中', '再依頼'])) {
            return view('inspections.invalid');
        }

        // バリデーション
        $validator = Validator::make($request->all(), [
            'results' => 'required|array',
            'results.*.check_result' => 'required|in:正常,要確認,異常',
            'results.*.comment' => 'nullable|string|max:1000',
            'results.*.photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            $overallResult = '正常';

            foreach ($validatedData['results'] as $itemId => $result) {
                $photoPath = null;
                if (isset($result['photo'])) {
                    $photoPath = $result['photo']->store('photos', 'public');
                }

                InspectionRecordDetail::create([
                    'inspection_record_id' => $record->id,
                    'inspection_item_id' => $itemId,
                    'check_result' => $result['check_result'],
                    'comment' => $result['comment'] ?? null,
                    'photo_path' => $photoPath,
                ]);

                if ($result['check_result'] === '異常') {
                    $overallResult = '異常';
                } elseif ($result['check_result'] === '要確認' && $overallResult !== '異常') {
                    $overallResult = '要確認';
                }
            }

            $record->update([
                'status' => '点検済み',
                'result' => $overallResult,
                'inspected_at' => now(),
                'one_time_token' => null,
                'token_expires_at' => null,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '報告処理中にエラーが発生しました。' . $e->getMessage())->withInput();
        }

        return view('inspections.complete');
    }
}
