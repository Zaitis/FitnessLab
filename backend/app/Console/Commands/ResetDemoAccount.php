<?php

namespace App\Console\Commands;

use App\Application\Demo\Actions\ResetDemoAccountAction;
use Illuminate\Console\Command;

class ResetDemoAccount extends Command
{
    protected $signature = 'demo:reset';

    protected $description = 'Wipe and re-seed the shared demo account to its known starting state';

    public function handle(ResetDemoAccountAction $action): void
    {
        $action->execute();

        $this->info('Demo account reset to its seeded state.');
    }
}
