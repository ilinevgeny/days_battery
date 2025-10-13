<?php

declare(strict_types=1);

namespace App\Battery\Application\Query\GetBatteryByHash;

final readonly class GetBatteryByHashQuery
{
    public function __construct(
        public string $publicHash,
    ) {
    }
}
