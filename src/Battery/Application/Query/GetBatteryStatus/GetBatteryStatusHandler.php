<?php

declare(strict_types=1);

namespace App\Battery\Application\Query\GetBatteryStatus;

use App\Battery\Application\DTO\BatteryStatusDTO;
use App\Battery\Domain\Entity\Battery;
use App\Battery\Domain\Repository\BatteryRepositoryInterface;
use App\Shared\Domain\UserId;

final readonly class GetBatteryStatusHandler
{
    public function __construct(
        private BatteryRepositoryInterface $batteryRepository,
    ) {
    }

    public function handle(GetBatteryStatusQuery $query): ?BatteryStatusDTO
    {
        $userId = UserId::fromString($query->userId);

        $battery = $this->batteryRepository->findTodayByUserId($userId);

        if ($battery === null) {
            // Create a default battery for display purposes
            $battery = Battery::createForToday($userId);
        }

        return new BatteryStatusDTO(
            capacitySeconds: $battery->getCapacity()->toSeconds(),
            totalUsedSeconds: $battery->getTotalUsedSeconds(),
            remainingSeconds: $battery->getRemainingSeconds(),
            percentage: $battery->getPercentage(),
            isActive: $battery->isActive(),
            currentSessionStartedAt: $battery->getCurrentSessionStartedAt(),
        );
    }
}
