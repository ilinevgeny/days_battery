<?php

declare(strict_types=1);

namespace App\Battery\Application\Command\StartBatterySession;

use App\Battery\Domain\Entity\Battery;
use App\Battery\Domain\Repository\BatteryRepositoryInterface;
use App\Shared\Domain\UserId;

final readonly class StartBatterySessionHandler
{
    public function __construct(
        private BatteryRepositoryInterface $batteryRepository,
    ) {}

    public function handle(StartBatterySessionCommand $command): void
    {
        $userId = UserId::fromString($command->userId);

        $battery = $this->batteryRepository->findTodayByUserId($userId);

        if ($battery === null) {
            $battery = Battery::createForToday($userId);
        }

        $battery->startSession();
        $this->batteryRepository->save($battery);
    }
}
