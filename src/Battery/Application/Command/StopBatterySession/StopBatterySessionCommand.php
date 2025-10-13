<?php

declare(strict_types=1);

namespace App\Battery\Application\Command\StopBatterySession;

final readonly class StopBatterySessionCommand
{
    public function __construct(
        public string $userId,
    ) {
    }
}
