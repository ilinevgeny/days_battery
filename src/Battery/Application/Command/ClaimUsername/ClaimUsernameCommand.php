<?php

declare(strict_types=1);

namespace App\Battery\Application\Command\ClaimUsername;

final readonly class ClaimUsernameCommand
{
    public function __construct(
        public string $username,
    ) {
    }
}
