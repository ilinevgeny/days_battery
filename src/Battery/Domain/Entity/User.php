<?php

declare(strict_types=1);

namespace App\Battery\Domain\Entity;

use App\Battery\Domain\ValueObject\PublicHash;
use App\Battery\Domain\ValueObject\Username;
use App\Shared\Domain\UserId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
#[ORM\Index(columns: ['username'], name: 'idx_username')]
#[ORM\Index(columns: ['public_hash'], name: 'idx_public_hash')]
class User
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 32, unique: true)]
    private string $username;

    #[ORM\Column(type: 'string', length: 32, unique: true, name: 'public_hash')]
    private string $publicHash;

    #[ORM\Column(type: 'datetime_immutable', name: 'created_at')]
    private \DateTimeImmutable $createdAt;

    private function __construct(
        UserId $id,
        Username $username,
        PublicHash $publicHash,
        \DateTimeImmutable $createdAt,
    ) {
        $this->id = $id->toString();
        $this->username = $username->toString();
        $this->publicHash = $publicHash->toString();
        $this->createdAt = $createdAt;
    }

    public static function create(Username $username): self
    {
        return new self(
            UserId::generate(),
            $username,
            PublicHash::generate(),
            new \DateTimeImmutable(),
        );
    }

    public function getId(): UserId
    {
        return UserId::fromString($this->id);
    }

    public function getUsername(): Username
    {
        return Username::fromString($this->username);
    }

    public function getPublicHash(): PublicHash
    {
        return PublicHash::fromString($this->publicHash);
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
