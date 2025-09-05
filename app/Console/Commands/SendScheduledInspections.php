<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InspectionRequest;
use App\Mail\InspectionRequestMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SendScheduledInspections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspections:send-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled inspection request emails for the current week.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for scheduled inspection requests to send...');

        // 今日の日付が月曜でなければ処理を終了
        if (!Carbon::today()->isMonday()) {
            $this->info('Today is not Monday. No requests to send.');
            return;
        }

        // 今日の日付を開始日とする、予約中の点検依頼を取得
        $requestsToSend = InspectionRequest::with('records.vehicle.users', 'pattern')
            ->where('status', 'scheduled')
            ->where('target_week_start', Carbon::today()->format('Y-m-d'))
            ->get();

        if ($requestsToSend->isEmpty()) {
            $this->info('No scheduled requests found for this week.');
            return;
        }

        foreach ($requestsToSend as $request) {
            $this->info("Sending request ID: {$request->id} for week of {$request->target_week_start}");

            DB::beginTransaction();
            try {
                foreach ($request->records as $record) {
                    if ($record->vehicle) { // 車両が存在する場合のみ
                        foreach ($record->vehicle->users as $user) {
                            if ($user->email) {
                                Mail::to($user->email)->send(new InspectionRequestMail($user, $record->vehicle, $record, $request));
                                $this->line(" - Sent email to {$user->email} for vehicle {$record->vehicle->model_name}");
                            }
                        }
                    }
                }

                // 送信が完了したらステータスを'sent'に更新
                $request->status = 'sent';
                $request->save();

                DB::commit();
                $this->info("Successfully sent and updated request ID: {$request->id}");
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Failed to send request ID: {$request->id}. Error: {$e->getMessage()}");
                \Log::error("Scheduled inspection email sending failed for request ID {$request->id}: " . $e->getMessage());
            }
        }

        $this->info('Finished sending scheduled inspection requests.');
    }
}
