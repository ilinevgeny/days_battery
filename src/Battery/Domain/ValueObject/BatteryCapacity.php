<?php

declare(strict_types=1);

namespace App\Battery\Domain\ValueObject;

final readonly class BatteryCapacity
{
    private const int DEFAULT_HOURS = 16;
    private const int MIN_HOURS = 1;
    private const int MAX_HOURS = 24;
    private const int SECONDS_PER_HOUR = 3600;

    private function __construct(
        private int $seconds,
    ) {
    }

    public static function default(): self
    {
        return self::fromHours(self::DEFAULT_HOURS);
    }

    public static function fromHours(int $hours): self
    {
        if ($hours < self::MIN_HOURS || $hours > self::MAX_HOURS) {
            throw new \InvalidArgumentException(sprintf(
                'Battery capacity must be between %d and %d hours',
                self::MIN_HOURS,
                self::MAX_HOURS,
            ));
        }

        return new self($hours * self::SECONDS_PER_HOUR);
    }

    public static function fromSeconds(int $seconds): self
    {
        $hours = (int) ($seconds / self::SECONDS_PER_HOUR);

        if ($hours < self::MIN_HOURS || $hours > self::MAX_HOURS) {
            throw new \InvalidArgumentException(sprintf(
                'Battery capacity must be between %d and %d hours',
                self::MIN_HOURS,
                self::MAX_HOURS,
            ));
        }

        return new self($seconds);
    }

    public function toSeconds(): int
    {
        return $this->seconds;
    }

    public function toHours(): int
    {
        return (int) ($this->seconds / self::SECONDS_PER_HOUR);
    }

    public function equals(self $other): bool
    {
        return $this->seconds === $other->seconds;
    }
}
