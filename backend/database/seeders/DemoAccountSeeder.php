<?php

namespace Database\Seeders;

use App\Application\Demo\Actions\ResetDemoAccountAction;
use Illuminate\Database\Seeder;

/**
 * Thin wrapper: the actual seeding/reset logic lives in
 * ResetDemoAccountAction, shared with the nightly demo:reset console
 * command so there is exactly one definition of "what the demo account
 * looks like". Requires ExerciseSeeder and MealTemplateSeeder to have
 * already run — the generated plans draw from those catalogues.
 */
class DemoAccountSeeder extends Seeder
{
    public function run(ResetDemoAccountAction $action): void
    {
        $action->execute();
    }
}
