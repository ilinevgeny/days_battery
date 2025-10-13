<?php

declare(strict_types=1);

namespace App\Battery\Infrastructure\Persistence\Doctrine;

use App\Battery\Domain\Entity\User;
use App\Battery\Domain\Repository\UserRepositoryInterface;
use App\Battery\Domain\ValueObject\PublicHash;
use App\Battery\Domain\ValueObject\Username;
use App\Shared\Domain\UserId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function findById(UserId $id): ?User
    {
        return $this->entityManager->find(User::class, $id->toString());
    }

    public function findByUsername(Username $username): ?User
    {
        return $this->entityManager->getRepository(User::class)
            ->findOneBy(['username' => $username->toString()]);
    }

    public function findByPublicHash(PublicHash $publicHash): ?User
    {
        return $this->entityManager->getRepository(User::class)
            ->findOneBy(['publicHash' => $publicHash->toString()]);
    }

    public function existsByUsername(Username $username): bool
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(u.id)')
            ->from(User::class, 'u')
            ->where('u.username = :username')
            ->setParameter('username', $username->toString());

        return ((int) $qb->getQuery()->getSingleScalarResult()) > 0;
    }
}
