<?php

declare(strict_types=1);

namespace App\Battery\Application\Command\StartBatterySession;

final readonly class StartBatterySessionCommand
{
    public function __construct(
        public string $userId,
    ) {
    }
}
