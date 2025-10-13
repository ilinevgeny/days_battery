<?php

declare(strict_types=1);

namespace App\Battery\Domain\Repository;

use App\Battery\Domain\Entity\User;
use App\Battery\Domain\ValueObject\PublicHash;
use App\Battery\Domain\ValueObject\Username;
use App\Shared\Domain\UserId;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function findById(UserId $id): ?User;

    public function findByUsername(Username $username): ?User;

    public function findByPublicHash(PublicHash $publicHash): ?User;

    public function existsByUsername(Username $username): bool;
}
