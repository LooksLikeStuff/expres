<?php

namespace App\Observers;

use App\Models\Brief;
use App\Services\Briefs\BriefService;

class BriefObserver
{
    public function __construct(
        private readonly BriefService $briefService
    )
    {
    }

    /**
     * Handle the Brief "created" event.
     */
    public function created(Brief $brief): void
    {
        $this->briefService->linkToAvailableDeal($brief);
    }

    /**
     * Handle the Brief "updated" event.
     */
    public function updated(Brief $brief): void
    {
        //
    }

    /**
     * Handle the Brief "deleted" event.
     */
    public function deleted(Brief $brief): void
    {
        //
    }

    /**
     * Handle the Brief "restored" event.
     */
    public function restored(Brief $brief): void
    {
        //
    }

    /**
     * Handle the Brief "force deleted" event.
     */
    public function forceDeleted(Brief $brief): void
    {
        //
    }
}
