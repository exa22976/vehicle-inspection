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
        $record = InspectionRecord::with('vehicle.users', 'inspectionRequest.pattern.items')->where('one_time_token', $token)->first();

        if (!$record || $record->token_expires_at < now() || !in_array($record->status, ['依頼中', '再依頼'])) {
            return view('inspections.invalid');
        }

        $inspectionRequest = $record->inspectionRequest;
        $vehicle = $record->vehicle;
        $pattern = $inspectionRequest->pattern;

        // ★★★★★ 担当者リストをビューに渡す ★★★★★
        $assignedUsers = $record->vehicle->users;

        // 点検項目を絞り込むロジック（ご提示いただいたコードをそのまま使用）
        $items = $pattern->items()
            ->where('category', $vehicle->category . '共通')
            ->orderBy('display_order')
            ->get();

        return view('inspections.form', compact('record', 'inspectionRequest', 'vehicle', 'pattern', 'items', 'assignedUsers'));
    }

    /**
     * 点検フォームの報告を処理する
     */
    public function submitForm(Request $request, string $token)
    {
        $record = InspectionRecord::with('vehicle.users')->where('one_time_token', $token)->first();

        if (!$record || $record->token_expires_at < now() || !in_array($record->status, ['依頼中', '再依頼'])) {
            return view('inspections.invalid');
        }

        // ★★★★★ ここから担当者IDの決定ロジックとバリデーションを修正 ★★★★★
        $assignedUsers = $record->vehicle->users;
        $inspectorId = null;

        // 基本のバリデーションルール
        $rules = [
            'results' => 'required|array',
            'results.*.check_result' => 'required|in:正常,要確認,異常',
            'results.*.comment' => 'nullable|string|max:1000',
            'results.*.photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ];

        // 担当者が複数いる場合は、フォームからの選択を必須にする
        if ($assignedUsers->count() > 1) {
            $rules['user_id'] = 'required|exists:users,id';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $validatedData = $validator->validated();

        // 点検実施者のIDを決定
        if ($assignedUsers->count() === 1) {
            $inspectorId = $assignedUsers->first()->id;
        } elseif ($assignedUsers->count() > 1) {
            $inspectorId = $validatedData['user_id'];
        } else {
            return back()->with('error', 'この車両には点検を実施できる担当者が割り当てられていません。');
        }
        // ★★★★★ ここまで修正 ★★★★★

        DB::beginTransaction();
        try {
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
                'user_id' => $inspectorId, // ★★★★★ 担当者IDを保存 ★★★★★
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
