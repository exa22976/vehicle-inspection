<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InspectionRecord;
use App\Models\InspectionRecordDetail;
use App\Models\User; // ★ User モデルを use
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // ★ Log を use
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // ★ Rule を use

class InspectionController extends Controller
{
    /**
     * Display the inspection form.
     */
    public function showForm(string $token)
    {
        // トークンに紐づく有効な点検記録を取得 (関連情報も Eager Load)
        $record = InspectionRecord::with(['vehicle.users', 'inspectionRequest.pattern.items'])
            ->where('one_time_token', $token)
            ->whereNotNull('token_expires_at') // 期限が設定されている
            ->where('token_expires_at', '>', now()) // 期限が切れていない
            ->whereIn('status', ['依頼中', '再依頼']) // ステータスが依頼中または再依頼
            ->first();

        // レコードが見つからない、または無効な場合
        if (!$record) {
            return view('inspections.invalid');
        }

        $inspectionRequest = $record->inspectionRequest;
        $vehicle = $record->vehicle;
        $pattern = $inspectionRequest->pattern;

        // ★★★★★ 車両に割り当てられている担当者リストを取得 ★★★★★
        $assignedUsers = $record->vehicle->users()->whereNull('deleted_at')->orderBy('name')->get();

        // 点検項目を取得 (カテゴリと表示順でソート)
        // カテゴリ名が「(車両カテゴリ)共通」である項目のみを取得
        $items = $pattern->items()
            ->where('category', $vehicle->category . '共通')
            ->orderBy('display_order') // display_order カラムでソート
            ->get();

        // カテゴリごとに項目をグループ化 (ビューでの表示用)
        $itemsGrouped = $items->groupBy('category');


        return view('inspections.form', compact(
            'record',
            'inspectionRequest',
            'vehicle',
            'pattern',
            'itemsGrouped', // ★★★★★ ここに itemsGrouped を追加 ★★★★★
            'assignedUsers' // ★ ビューに担当者リストを渡す
        ));
    }

    /**
     * Submit the inspection form.
     */
    public function submitForm(Request $request, string $token)
    {
        // トークンに紐づく有効な点検記録を再取得
        $record = InspectionRecord::where('one_time_token', $token)
            ->whereNotNull('token_expires_at')
            ->where('token_expires_at', '>', now())
            ->whereIn('status', ['依頼中', '再依頼'])
            ->first();

        // レコードが見つからない、または無効な場合
        if (!$record) {
            return redirect()->route('inspection.invalid')->with('error', 'この点検依頼は無効か、既に報告済み、または有効期限が切れています。');
        }

        $pattern = $record->inspectionRequest->pattern;
        $items = $pattern->items()
            ->where('category', $record->vehicle->category . '共通') // showForm と同じ条件で項目を取得
            ->get();

        // --- バリデーション ---
        $rules = [
            'inspector_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->whereNull('deleted_at'),
                Rule::exists('vehicle_user', 'user_id')->where('vehicle_id', $record->vehicle_id),
            ],
            // ★★★ 走行距離のバリデーションを削除 ★★★
            // 'mileage' => 'nullable|integer|min:0',
            'overall_remarks' => 'nullable|string|max:1000',
        ];

        $messages = [
            'inspector_id.required' => '点検実施者を選択してください。',
            'inspector_id.exists' => '選択された担当者が無効です。車両に割り当てられている担当者を選択してください。',
        ];

        // 各点検項目の入力に対するバリデーションルールとメッセージを追加
        foreach ($items as $item) {
            $rules['results.' . $item->id . '.check_result'] = 'required|in:正常,要確認,異常';
            $rules['results.' . $item->id . '.comment'] = [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(function () use ($request, $item) {
                    $result = $request->input('results.' . $item->id . '.check_result');
                    return in_array($result, ['要確認', '異常']);
                }),
            ];
            $rules['results.' . $item->id . '.photo'] = 'nullable|image|max:5120';

            $messages['results.' . $item->id . '.check_result.required'] = $item->item_name . ' の結果を選択してください。';
            $messages['results.' . $item->id . '.comment.required'] = $item->item_name . ' の結果が「要確認」または「異常」の場合、状況報告は必須です。';
            $messages['results.' . $item->id . '.photo.image'] = $item->item_name . ' の添付ファイルは画像（jpg, png, gifなど）である必要があります。';
            $messages['results.' . $item->id . '.photo.max'] = $item->item_name . ' の添付ファイルのサイズは5MB以下にしてください。';
        }

        // バリデーション実行
        $validatedData = $request->validate($rules, $messages);

        // --- データベース処理 ---
        try {
            DB::beginTransaction();

            $overallResult = '正常';

            foreach ($items as $item) {
                $itemResultData = $validatedData['results'][$item->id];
                $photoPath = null;

                if ($request->hasFile('results.' . $item->id . '.photo')) {
                    $uploadedFile = $request->file('results.' . $item->id . '.photo');
                    if ($uploadedFile && $uploadedFile->isValid()) {
                        $directory = 'photos/' . now()->format('Y-m');
                        $photoPath = $uploadedFile->store($directory, 'public');
                        if (!$photoPath) {
                            throw new \Exception("写真の保存に失敗しました ({$item->item_name})。ディスク容量や権限を確認してください。");
                        }
                    } else {
                        Log::warning("Invalid photo uploaded for item ID {$item->id} in record ID {$record->id}.");
                    }
                }

                InspectionRecordDetail::updateOrCreate(
                    [
                        'inspection_record_id' => $record->id,
                        'inspection_item_id' => $item->id,
                    ],
                    [
                        'check_result' => $itemResultData['check_result'],
                        'comment' => $itemResultData['comment'] ?? null,
                        'photo_path' => $photoPath,
                    ]
                );

                if ($itemResultData['check_result'] === '異常') {
                    $overallResult = '異常';
                } elseif ($itemResultData['check_result'] === '要確認' && $overallResult !== '異常') {
                    $overallResult = '要確認';
                }
            }

            // InspectionRecord を更新
            $record->update([
                'user_id' => $validatedData['inspector_id'],
                // ★★★ 走行距離の保存処理を削除 ★★★
                // 'mileage' => $validatedData['mileage'] ?? null,
                'remarks' => $validatedData['overall_remarks'] ?? null,
                'status' => '点検済み',
                'result' => $overallResult,
                'inspected_at' => now(),
                'one_time_token' => null,
                'token_expires_at' => null,
            ]);

            DB::commit();

            return redirect()->route('inspection.complete');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Inspection submission failed for record ID {$record->id}: " . $e->getMessage(), [
                'exception' => $e
            ]);
            $errorMessage = '報告処理中に予期せぬエラーが発生しました。';
            if (str_contains($e->getMessage(), '写真の保存に失敗')) {
                $errorMessage .= '写真のアップロード処理で問題が発生した可能性があります。ファイルサイズや形式を確認してください。';
            } else {
                $errorMessage .= '時間を置いて再度お試しいただくか、管理者に連絡してください。';
            }
            return back()->with('error', $errorMessage)->withInput();
        }
    }

    /**
     * Display the completion page.
     */
    public function complete()
    {
        return view('inspections.complete');
    }

    /**
     * Display the invalid token page.
     */
    public function invalid()
    {
        return view('inspections.invalid');
    }
}
