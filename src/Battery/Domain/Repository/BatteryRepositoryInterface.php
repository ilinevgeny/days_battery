<?php

declare(strict_types=1);

namespace App\Battery\Domain\Repository;

use App\Battery\Domain\Entity\Battery;
use App\Shared\Domain\UserId;

interface BatteryRepositoryInterface
{
    public function save(Battery $battery): void;

    public function findById(string $id): ?Battery;

    public function findTodayByUserId(UserId $userId): ?Battery;

    public function findByUserIdAndDate(UserId $userId, \DateTimeImmutable $date): ?Battery;
}
