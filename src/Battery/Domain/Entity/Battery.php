<?php

declare(strict_types=1);

namespace App\Battery\Domain\Entity;

use App\Battery\Domain\Exception\BatteryAlreadyActiveException;
use App\Battery\Domain\Exception\BatteryNotActiveException;
use App\Battery\Domain\ValueObject\BatteryCapacity;
use App\Shared\Domain\UserId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'batteries')]
#[ORM\Index(columns: ['user_id', 'date'], name: 'idx_user_date')]
class Battery
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 36, name: 'user_id')]
    private string $userId;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $date;

    #[ORM\Column(type: 'integer', name: 'capacity_seconds')]
    private int $capacitySeconds;

    #[ORM\Column(type: 'boolean', name: 'is_active')]
    private bool $isActive;

    #[ORM\Column(type: 'datetime_immutable', nullable: true, name: 'current_session_started_at')]
    private ?\DateTimeImmutable $currentSessionStartedAt;

    #[ORM\Column(type: 'integer', name: 'total_used_seconds')]
    private int $totalUsedSeconds;

    #[ORM\Column(type: 'datetime_immutable', name: 'created_at')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', name: 'updated_at')]
    private \DateTimeImmutable $updatedAt;

    private function __construct(
        string $id,
        UserId $userId,
        \DateTimeImmutable $date,
        BatteryCapacity $capacity,
        bool $isActive,
        ?\DateTimeImmutable $currentSessionStartedAt,
        int $totalUsedSeconds,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ) {
        $this->id = $id;
        $this->userId = $userId->toString();
        $this->date = $date;
        $this->capacitySeconds = $capacity->toSeconds();
        $this->isActive = $isActive;
        $this->currentSessionStartedAt = $currentSessionStartedAt;
        $this->totalUsedSeconds = $totalUsedSeconds;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function createForToday(UserId $userId, ?BatteryCapacity $capacity = null): self
    {
        $now = new \DateTimeImmutable();
        $today = new \DateTimeImmutable($now->format('Y-m-d'));

        return new self(
            id: \Symfony\Component\Uid\Uuid::v4()->toRfc4122(),
            userId: $userId,
            date: $today,
            capacity: $capacity ?? BatteryCapacity::default(),
            isActive: false,
            currentSessionStartedAt: null,
            totalUsedSeconds: 0,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public function startSession(): void
    {
        if ($this->isActive) {
            throw new BatteryAlreadyActiveException('Battery session is already active');
        }

        $this->isActive = true;
        $this->currentSessionStartedAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function stopSession(): void
    {
        if (!$this->isActive) {
            throw new BatteryNotActiveException('Battery session is not active');
        }

        if ($this->currentSessionStartedAt === null) {
            throw new \LogicException('Current session start time is null while battery is active');
        }

        $now = new \DateTimeImmutable();
        $sessionDuration = $now->getTimestamp() - $this->currentSessionStartedAt->getTimestamp();
        $this->totalUsedSeconds += $sessionDuration;

        $this->isActive = false;
        $this->currentSessionStartedAt = null;
        $this->updatedAt = $now;
    }

    public function updateCapacity(BatteryCapacity $capacity): void
    {
        $this->capacitySeconds = $capacity->toSeconds();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getCurrentUsedSeconds(): int
    {
        $used = $this->totalUsedSeconds;

        if ($this->isActive && $this->currentSessionStartedAt !== null) {
            $now = new \DateTimeImmutable();
            $used += $now->getTimestamp() - $this->currentSessionStartedAt->getTimestamp();
        }

        return $used;
    }

    public function getRemainingSeconds(): int
    {
        $remaining = $this->capacitySeconds - $this->getCurrentUsedSeconds();

        return max(0, $remaining);
    }

    public function getPercentage(): float
    {
        if ($this->capacitySeconds === 0) {
            return 0.0;
        }

        $remaining = $this->getRemainingSeconds();

        return round(($remaining / $this->capacitySeconds) * 100, 2);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): UserId
    {
        return UserId::fromString($this->userId);
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getCapacity(): BatteryCapacity
    {
        return BatteryCapacity::fromSeconds($this->capacitySeconds);
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCurrentSessionStartedAt(): ?\DateTimeImmutable
    {
        return $this->currentSessionStartedAt;
    }

    public function getTotalUsedSeconds(): int
    {
        return $this->totalUsedSeconds;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
