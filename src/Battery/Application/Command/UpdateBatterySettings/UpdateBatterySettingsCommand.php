<?php

declare(strict_types=1);

namespace App\Battery\Application\Command\UpdateBatterySettings;

final readonly class UpdateBatterySettingsCommand
{
    public function __construct(
        public string $userId,
        public int $capacityHours,
    ) {
    }
}
