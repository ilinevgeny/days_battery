<?php

declare(strict_types=1);

namespace App\Battery\Application\Command\UpdateBatterySettings;

use App\Battery\Domain\Exception\BatteryNotFoundException;
use App\Battery\Domain\Repository\BatteryRepositoryInterface;
use App\Battery\Domain\ValueObject\BatteryCapacity;
use App\Shared\Domain\UserId;

final readonly class UpdateBatterySettingsHandler
{
    public function __construct(
        private BatteryRepositoryInterface $batteryRepository,
    ) {
    }

    public function handle(UpdateBatterySettingsCommand $command): void
    {
        $userId = UserId::fromString($command->userId);
        $capacity = BatteryCapacity::fromHours($command->capacityHours);

        $battery = $this->batteryRepository->findTodayByUserId($userId);

        if ($battery === null) {
            throw new BatteryNotFoundException('Battery not found for today');
        }

        $battery->updateCapacity($capacity);
        $this->batteryRepository->save($battery);
    }
}
