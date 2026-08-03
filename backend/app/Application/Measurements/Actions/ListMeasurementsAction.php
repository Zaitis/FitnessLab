<?php

namespace App\Application\Measurements\Actions;

use App\Models\BmiMeasurement;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListMeasurementsAction
{
    /**
     * @return LengthAwarePaginator<int, BmiMeasurement>
     */
    public function execute(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $user->bmiMeasurements()
            ->orderByDesc('measured_on')
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
