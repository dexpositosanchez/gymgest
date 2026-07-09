<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\RoutineAssignment\UseCases\UpdateCurrentRoutinesUseCase;
use Illuminate\Console\Command;

class UpdateCurrentRoutines extends Command
{
    protected $signature = 'routines:update-current';

    protected $description = 'Update current routines based on starts_at date';

    private UpdateCurrentRoutinesUseCase $updateCurrentRoutinesUseCase;

    public function __construct(UpdateCurrentRoutinesUseCase $updateCurrentRoutinesUseCase)
    {
        parent::__construct();
        $this->updateCurrentRoutinesUseCase = $updateCurrentRoutinesUseCase;
    }

    public function handle(): int
    {
        $this->info('Updating current routines...');

        $updatedCount = $this->updateCurrentRoutinesUseCase->execute();

        $this->info("Updated {$updatedCount} routine assignments to current status");

        return 0;
    }
}
