<?php

declare(strict_types=1);

namespace App\Battery\Application\DTO;

final readonly class PublicBatteryDTO
{
    public function __construct(
        public string $username,
        public int $capacitySeconds,
        public int $totalUsedSeconds,
        public int $remainingSeconds,
        public float $percentage,
        public bool $isActive,
        public ?\DateTimeImmutable $lastUpdated,
    ) {
    }

    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'capacity' => $this->capacitySeconds,
            'totalUsed' => $this->totalUsedSeconds,
            'remaining' => $this->remainingSeconds,
            'percentage' => $this->percentage,
            'isActive' => $this->isActive,
            'lastUpdated' => $this->lastUpdated?->format(\DateTimeInterface::ATOM),
        ];
    }
}
