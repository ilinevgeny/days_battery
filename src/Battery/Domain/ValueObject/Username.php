<?php

declare(strict_types=1);

namespace App\Battery\Domain\ValueObject;

final readonly class Username
{
    private const int MIN_LENGTH = 3;
    private const int MAX_LENGTH = 32;

    private function __construct(
        private string $value,
    ) {
    }

    public static function fromString(string $value): self
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new \InvalidArgumentException('Username cannot be empty');
        }

        if (mb_strlen($trimmed) < self::MIN_LENGTH) {
            throw new \InvalidArgumentException(sprintf(
                'Username must be at least %d characters long',
                self::MIN_LENGTH,
            ));
        }

        if (mb_strlen($trimmed) > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(sprintf(
                'Username must not exceed %d characters',
                self::MAX_LENGTH,
            ));
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $trimmed)) {
            throw new \InvalidArgumentException('Username can only contain letters, numbers, underscores and hyphens');
        }

        return new self($trimmed);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
