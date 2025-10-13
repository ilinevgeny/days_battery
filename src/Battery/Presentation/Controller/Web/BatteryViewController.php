<?php

declare(strict_types=1);

namespace App\Battery\Presentation\Controller\Web;

use App\Battery\Application\Query\GetBatteryByHash\GetBatteryByHashHandler;
use App\Battery\Application\Query\GetBatteryByHash\GetBatteryByHashQuery;
use App\Battery\Domain\Exception\BatteryNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BatteryViewController extends AbstractController
{
    public function __construct(
        private readonly GetBatteryByHashHandler $getBatteryByHashHandler,
    ) {
    }

    #[Route('/battery/{hash}', name: 'battery_public_view', methods: ['GET'])]
    public function publicView(string $hash): Response
    {
        try {
            $battery = $this->getBatteryByHashHandler->handle(
                new GetBatteryByHashQuery($hash)
            );

            return $this->render('battery/public_view.html.twig', [
                'battery' => $battery,
            ]);
        } catch (BatteryNotFoundException) {
            throw $this->createNotFoundException('Battery not found');
        }
    }
}
