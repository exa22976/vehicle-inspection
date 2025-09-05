<?php

namespace App\Mail;

use App\Models\InspectionRecord;
use App\Models\InspectionRequest;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InspectionRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        public User $user,
        public Vehicle $vehicle,
        public InspectionRecord $record,
        public InspectionRequest $inspectionRequest
    ) {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('【' . config('app.name') . '】週次点検のお願い')
            ->view('emails.inspection-request');
    }
}
