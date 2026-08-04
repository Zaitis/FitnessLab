<?php

namespace App\Application\Adherence\Actions;

use App\Models\AdherenceEntry;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class GetMonthAdherenceAction
{
    /**
     * @return Collection<int, AdherenceEntry>
     */
    public function execute(User $user, Carbon $month): Collection
    {
        return $user->adherenceEntries()
            ->whereBetween('entry_date', [
                $month->clone()->startOfMonth()->toDateString(),
                $month->clone()->endOfMonth()->toDateString(),
            ])
            ->get();
    }
}
