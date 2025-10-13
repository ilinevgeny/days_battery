<?php

declare(strict_types=1);

namespace App\Battery\Domain\ValueObject;

final readonly class PublicHash
{
    private const int HASH_LENGTH = 32;

    private function __construct(
        private string $value,
    ) {
    }

    public static function generate(): self
    {
        return new self(bin2hex(random_bytes(self::HASH_LENGTH / 2)));
    }

    public static function fromString(string $value): self
    {
        if (strlen($value) !== self::HASH_LENGTH) {
            throw new \InvalidArgumentException(sprintf(
                'PublicHash must be exactly %d characters long',
                self::HASH_LENGTH,
            ));
        }

        if (!ctype_xdigit($value)) {
            throw new \InvalidArgumentException('PublicHash must contain only hexadecimal characters');
        }

        return new self($value);
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
