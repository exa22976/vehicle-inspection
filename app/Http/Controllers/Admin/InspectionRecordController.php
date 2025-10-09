<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InspectionRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\InspectionRequestMail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InspectionRecordController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InspectionRecord  $inspectionRecord
     * @return \Illuminate\View\View
     */
    public function show(InspectionRecord $inspectionRecord)
    {
        $inspectionRecord->load('details.item', 'vehicle', 'user', 'adminComments.user', 'inspectionRequest');

        $historicalRecords = InspectionRecord::where('vehicle_id', $inspectionRecord->vehicle_id)
            ->where('id', '!=', $inspectionRecord->id)
            ->whereNotNull('inspected_at')
            ->orderBy('inspected_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.records.show', compact('inspectionRecord', 'historicalRecords'));
    }


    /**
     * Update the status of the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InspectionRecord  $inspectionRecord
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, InspectionRecord $inspectionRecord)
    {
        $validated = $request->validate([
            'issue_status' => 'required|in:未対応,対応済み',
            'resolved_at' => 'nullable|date',
            'admin_comment' => 'nullable|string|max:1000',
        ]);

        $inspectionRecord->issue_status = $validated['issue_status'];
        $inspectionRecord->resolved_at = $validated['resolved_at'];
        $inspectionRecord->save();

        if (!empty($validated['admin_comment'])) {
            $inspectionRecord->adminComments()->create([
                'user_id' => Auth::id(),
                'comment' => $validated['admin_comment'],
            ]);
        }

        return redirect()->route('admin.records.show', $inspectionRecord)
            ->with('success', '対応状況を更新しました。');
    }

    /**
     * Resend the inspection request and create a new record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InspectionRecord  $inspectionRecord
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reRequest(Request $request, InspectionRecord $inspectionRecord)
    {
        DB::beginTransaction();
        try {
            $inspectionRequest = $inspectionRecord->inspectionRequest;
            if (!$inspectionRequest) {
                throw new \Exception('親の点検依頼が見つかりません。データが不正な状態です。');
            }

            $inspectionRequest->records()
                ->where('vehicle_id', $inspectionRecord->vehicle_id)
                ->update(['is_latest' => false]);

            $newRecord = $inspectionRequest->records()->create([
                'vehicle_id' => $inspectionRecord->vehicle_id,
                'status' => '再依頼',
                'one_time_token' => Str::random(40),
                'token_expires_at' => Carbon::now()->addDays(7),
                'is_latest' => true,
            ]);

            // --- メール通知処理 ---
            $vehicle = $newRecord->vehicle;
            if ($vehicle && !$vehicle->users->isEmpty()) {
                foreach ($vehicle->users as $user) {
                    if ($user->email) {
                        // ★★★★★ ここを修正 ★★★★★
                        // InspectionRequestMail のコンストラクタに合わせて引数を渡す
                        Mail::to($user->email)->send(new InspectionRequestMail(
                            $user,
                            $vehicle,
                            $newRecord,
                            $inspectionRequest
                        ));
                    }
                }
            }
            // --- ここまで ---

            DB::commit();

            $message = $newRecord->vehicle->model_name . ' の再点検依頼を作成し、担当者に通知しました。';
            return redirect()->route('admin.dashboard', ['week' => $inspectionRequest->target_week_start])
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('再点検依頼作成エラー: ' . $e->getMessage());
            return back()->with('error', '再点検依頼の作成中にエラーが発生しました。' . $e->getMessage());
        }
    }
}
