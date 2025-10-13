<?php

declare(strict_types=1);

namespace App\Battery\Application\DTO;

final readonly class ClaimUsernameResultDTO
{
    public function __construct(
        public string $userId,
        public string $username,
        public string $publicHash,
    ) {
    }

    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
            'username' => $this->username,
            'publicHash' => $this->publicHash,
        ];
    }
}
