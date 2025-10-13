<?php

declare(strict_types=1);

namespace App\Battery\Infrastructure\Persistence\Doctrine;

use App\Battery\Domain\Entity\Battery;
use App\Battery\Domain\Repository\BatteryRepositoryInterface;
use App\Shared\Domain\UserId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineBatteryRepository implements BatteryRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Battery $battery): void
    {
        $this->entityManager->persist($battery);
        $this->entityManager->flush();
    }

    public function findById(string $id): ?Battery
    {
        return $this->entityManager->find(Battery::class, $id);
    }

    public function findTodayByUserId(UserId $userId): ?Battery
    {
        $today = new \DateTimeImmutable('today');

        return $this->findByUserIdAndDate($userId, $today);
    }

    public function findByUserIdAndDate(UserId $userId, \DateTimeImmutable $date): ?Battery
    {
        return $this->entityManager->getRepository(Battery::class)
            ->findOneBy([
                'userId' => $userId->toString(),
                'date' => $date,
            ]);
    }
}
