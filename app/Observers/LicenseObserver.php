<?php

namespace App\Observers;

use App\Enums\LicenseStatus;
use App\Models\License;

class LicenseObserver
{
    /**
     * Handle the License "created" event.
     */
    public function created(License $license): void
    {
        //
    }

    /**
     * Handle the License "updated" event.
     */
    public function updated(License $license): void
    {
        // Check if license has expired and needs status update
        if ($license->isExpired() && $license->status !== LicenseStatus::EXPIRED) {
            $license->status = LicenseStatus::EXPIRED;
            $license->save();
        }
    }

    /**
     * Handle the License "deleted" event.
     */
    public function deleted(License $license): void
    {
        //
    }

    /**
     * Handle the License "restored" event.
     */
    public function restored(License $license): void
    {
        //
    }

    /**
     * Handle the License "force deleted" event.
     */
    public function forceDeleted(License $license): void
    {
        //
    }
}
