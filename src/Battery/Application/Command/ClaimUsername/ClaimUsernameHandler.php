<?php

declare(strict_types=1);

namespace App\Battery\Application\Command\ClaimUsername;

use App\Battery\Application\DTO\ClaimUsernameResultDTO;
use App\Battery\Domain\Entity\Battery;
use App\Battery\Domain\Entity\User;
use App\Battery\Domain\Exception\UsernameAlreadyTakenException;
use App\Battery\Domain\Repository\BatteryRepositoryInterface;
use App\Battery\Domain\Repository\UserRepositoryInterface;
use App\Battery\Domain\ValueObject\Username;

final readonly class ClaimUsernameHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private BatteryRepositoryInterface $batteryRepository,
    ) {
    }

    public function handle(ClaimUsernameCommand $command): ClaimUsernameResultDTO
    {
        $username = Username::fromString($command->username);

        if ($this->userRepository->existsByUsername($username)) {
            throw new UsernameAlreadyTakenException(sprintf(
                'Username "%s" is already taken',
                $username->toString(),
            ));
        }

        $user = User::create($username);
        $this->userRepository->save($user);

        $battery = Battery::createForToday($user->getId());
        $this->batteryRepository->save($battery);

        return new ClaimUsernameResultDTO(
            userId: $user->getId()->toString(),
            username: $user->getUsername()->toString(),
            publicHash: $user->getPublicHash()->toString(),
        );
    }
}
