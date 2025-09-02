<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InspectionRequest;
use App\Models\Vehicle;
use App\Mail\InspectionRequestMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InspectionRequestController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'inspection_pattern_id' => 'required|exists:inspection_patterns,id',
            'remarks' => 'nullable|string',
            'target_week_start' => 'required|date_format:Y-m-d',
        ]);

        if (InspectionRequest::where('target_week_start', $validated['target_week_start'])->exists()) {
            return redirect()->back()->with('error', 'この週の点検依頼は既に作成されています。');
        }

        $vehicles = Vehicle::with('users')->get();
        if ($vehicles->isEmpty()) {
            return redirect()->back()->with('error', '点検対象の車両が登録されていません。');
        }

        DB::beginTransaction();
        try {
            $inspectionRequest = InspectionRequest::create([
                'inspection_pattern_id' => $validated['inspection_pattern_id'],
                'remarks' => $validated['remarks'],
                'target_week_start' => $validated['target_week_start'],
            ]);

            foreach ($vehicles as $vehicle) {
                $record = $inspectionRequest->records()->create([
                    'vehicle_id' => $vehicle->id,
                    'status' => '依頼中',
                    'one_time_token' => Str::random(40),
                    'token_expires_at' => Carbon::parse($validated['target_week_start'])->addDays(7),
                ]);

                foreach ($vehicle->users as $user) {
                    if ($user->email) {
                        Mail::to($user->email)->send(new InspectionRequestMail($user, $vehicle, $record, $inspectionRequest));
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.dashboard', ['week' => $validated['target_week_start']])
                ->with('success', '点検依頼を作成しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('点検依頼作成エラー: ' . $e->getMessage());
            return redirect()->back()->with('error', '点検依頼の作成中に予期せぬエラーが発生しました。');
        }
    }

    public function destroy(InspectionRequest $inspectionRequest)
    {
        $targetWeek = $inspectionRequest->target_week_start;
        $inspectionRequest->delete();

        return redirect()->route('admin.dashboard', ['week' => $targetWeek])
            ->with('success', '点検依頼を削除しました。');
    }
}
