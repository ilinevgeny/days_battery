<?php

declare(strict_types=1);

namespace App\Battery\Application\Query\GetBatteryStatus;

final readonly class GetBatteryStatusQuery
{
    public function __construct(
        public string $userId,
    ) {
    }
}
