<?php

declare(strict_types=1);

namespace App\Battery\Presentation\Controller\Api;

use App\Battery\Application\Command\ClaimUsername\ClaimUsernameCommand;
use App\Battery\Application\Command\ClaimUsername\ClaimUsernameHandler;
use App\Battery\Application\Command\StartBatterySession\StartBatterySessionCommand;
use App\Battery\Application\Command\StartBatterySession\StartBatterySessionHandler;
use App\Battery\Application\Command\StopBatterySession\StopBatterySessionCommand;
use App\Battery\Application\Command\StopBatterySession\StopBatterySessionHandler;
use App\Battery\Application\Command\UpdateBatterySettings\UpdateBatterySettingsCommand;
use App\Battery\Application\Command\UpdateBatterySettings\UpdateBatterySettingsHandler;
use App\Battery\Application\Query\GetBatteryByHash\GetBatteryByHashHandler;
use App\Battery\Application\Query\GetBatteryByHash\GetBatteryByHashQuery;
use App\Battery\Application\Query\GetBatteryStatus\GetBatteryStatusHandler;
use App\Battery\Application\Query\GetBatteryStatus\GetBatteryStatusQuery;
use App\Battery\Domain\Exception\BatteryAlreadyActiveException;
use App\Battery\Domain\Exception\BatteryNotActiveException;
use App\Battery\Domain\Exception\BatteryNotFoundException;
use App\Battery\Domain\Exception\UsernameAlreadyTakenException;
use App\Battery\Domain\Exception\UserNotFoundException;
use App\Battery\Presentation\Request\ClaimUsernameRequest;
use App\Battery\Presentation\Request\UpdateSettingsRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class BatteryController extends AbstractController
{
    #[Route('/claim', name: 'api_claim_username', methods: ['POST'])]
    public function claim(
        #[MapRequestPayload] ClaimUsernameRequest $request,
        ClaimUsernameHandler $handler,
        SessionInterface $session,
    ): JsonResponse {
        try {
            $command = new ClaimUsernameCommand($request->username);
            $result = $handler->handle($command);

            $session->set('user_id', $result->userId);
            $session->set('username', $result->username);

            return $this->json([
                'success' => true,
                'data' => $result->toArray(),
            ], Response::HTTP_CREATED);
        } catch (UsernameAlreadyTakenException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_CONFLICT);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/battery/start', name: 'api_battery_start', methods: ['POST'])]
    public function start(
        StartBatterySessionHandler $handler,
        SessionInterface $session,
    ): JsonResponse {
        $userId = $session->get('user_id');

        if ($userId === null) {
            return $this->json([
                'success' => false,
                'error' => 'Not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $command = new StartBatterySessionCommand($userId);
            $handler->handle($command);

            return $this->json([
                'success' => true,
                'message' => 'Battery session started',
            ]);
        } catch (BatteryAlreadyActiveException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_CONFLICT);
        }
    }

    #[Route('/battery/stop', name: 'api_battery_stop', methods: ['POST'])]
    public function stop(
        StopBatterySessionHandler $handler,
        SessionInterface $session,
    ): JsonResponse {
        $userId = $session->get('user_id');

        if ($userId === null) {
            return $this->json([
                'success' => false,
                'error' => 'Not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $command = new StopBatterySessionCommand($userId);
            $handler->handle($command);

            return $this->json([
                'success' => true,
                'message' => 'Battery session stopped',
            ]);
        } catch (BatteryNotActiveException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_CONFLICT);
        } catch (BatteryNotFoundException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/battery/status', name: 'api_battery_status', methods: ['GET'])]
    public function status(
        GetBatteryStatusHandler $handler,
        SessionInterface $session,
    ): JsonResponse {
        $userId = $session->get('user_id');

        if ($userId === null) {
            return $this->json([
                'success' => false,
                'error' => 'Not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $query = new GetBatteryStatusQuery($userId);
        $status = $handler->handle($query);

        if ($status === null) {
            return $this->json([
                'success' => false,
                'error' => 'Battery not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'success' => true,
            'data' => $status->toArray(),
        ]);
    }

    #[Route('/battery/settings', name: 'api_battery_settings', methods: ['PATCH'])]
    public function updateSettings(
        #[MapRequestPayload] UpdateSettingsRequest $request,
        UpdateBatterySettingsHandler $handler,
        SessionInterface $session,
    ): JsonResponse {
        $userId = $session->get('user_id');

        if ($userId === null) {
            return $this->json([
                'success' => false,
                'error' => 'Not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $command = new UpdateBatterySettingsCommand($userId, $request->capacityHours);
            $handler->handle($command);

            return $this->json([
                'success' => true,
                'message' => 'Battery settings updated',
            ]);
        } catch (BatteryNotFoundException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/battery/public/{hash}', name: 'api_battery_public', methods: ['GET'])]
    public function publicView(
        string $hash,
        GetBatteryByHashHandler $handler,
    ): JsonResponse {
        try {
            $query = new GetBatteryByHashQuery($hash);
            $battery = $handler->handle($query);

            return $this->json([
                'success' => true,
                'data' => $battery->toArray(),
            ]);
        } catch (UserNotFoundException | \InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'error' => 'Battery not found',
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
