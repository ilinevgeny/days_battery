<?php

declare(strict_types=1);

namespace App\Battery\Application\Query\GetBatteryByHash;

use App\Battery\Application\DTO\PublicBatteryDTO;
use App\Battery\Domain\Exception\UserNotFoundException;
use App\Battery\Domain\Repository\BatteryRepositoryInterface;
use App\Battery\Domain\Repository\UserRepositoryInterface;
use App\Battery\Domain\ValueObject\PublicHash;

final readonly class GetBatteryByHashHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private BatteryRepositoryInterface $batteryRepository,
    ) {
    }

    public function handle(GetBatteryByHashQuery $query): PublicBatteryDTO
    {
        $publicHash = PublicHash::fromString($query->publicHash);

        $user = $this->userRepository->findByPublicHash($publicHash);

        if ($user === null) {
            throw new UserNotFoundException('User not found');
        }

        $battery = $this->batteryRepository->findTodayByUserId($user->getId());

        if ($battery === null) {
            throw new UserNotFoundException('Battery not found for today');
        }

        return new PublicBatteryDTO(
            username: $user->getUsername()->toString(),
            capacitySeconds: $battery->getCapacity()->toSeconds(),
            totalUsedSeconds: $battery->getTotalUsedSeconds(),
            remainingSeconds: $battery->getRemainingSeconds(),
            percentage: $battery->getPercentage(),
            isActive: $battery->isActive(),
            lastUpdated: $battery->getUpdatedAt(),
        );
    }
}
