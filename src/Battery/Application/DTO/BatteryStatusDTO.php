<?php

declare(strict_types=1);

namespace App\Battery\Application\DTO;

final readonly class BatteryStatusDTO
{
    public function __construct(
        public int $capacitySeconds,
        public int $totalUsedSeconds,
        public int $remainingSeconds,
        public float $percentage,
        public bool $isActive,
        public ?\DateTimeImmutable $currentSessionStartedAt,
    ) {
    }

    public function toArray(): array
    {
        return [
            'capacity' => $this->capacitySeconds,
            'totalUsed' => $this->totalUsedSeconds,
            'remaining' => $this->remainingSeconds,
            'percentage' => $this->percentage,
            'isActive' => $this->isActive,
            'currentSessionStartedAt' => $this->currentSessionStartedAt?->format(\DateTimeInterface::ATOM),
        ];
    }
}
