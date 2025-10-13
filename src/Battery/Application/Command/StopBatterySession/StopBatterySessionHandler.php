<?php

declare(strict_types=1);

namespace App\Battery\Application\Command\StopBatterySession;

use App\Battery\Domain\Exception\BatteryNotFoundException;
use App\Battery\Domain\Repository\BatteryRepositoryInterface;
use App\Shared\Domain\UserId;

final readonly class StopBatterySessionHandler
{
    public function __construct(
        private BatteryRepositoryInterface $batteryRepository,
    ) {
    }

    public function handle(StopBatterySessionCommand $command): void
    {
        $userId = UserId::fromString($command->userId);

        $battery = $this->batteryRepository->findTodayByUserId($userId);

        if ($battery === null) {
            throw new BatteryNotFoundException('Battery not found for today');
        }

        $battery->stopSession();
        $this->batteryRepository->save($battery);
    }
}
