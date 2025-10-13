<?php

declare(strict_types=1);

namespace App\Battery\Presentation\EventListener;

use App\Battery\Domain\Exception\BatteryNotFoundException;
use App\Battery\Domain\Exception\UserNotFoundException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 0)]
final readonly class ExceptionListener
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        // Skip if not HTML request (API calls should return JSON)
        if (!$this->isHtmlRequest($request->getRequestFormat())) {
            return;
        }

        // Convert domain exceptions to HTTP exceptions
        if ($exception instanceof BatteryNotFoundException || $exception instanceof UserNotFoundException) {
            $exception = new NotFoundHttpException($exception->getMessage(), $exception);
        }

        // Handle InvalidArgumentException (validation errors) as 404
        if ($exception instanceof \InvalidArgumentException) {
            $exception = new NotFoundHttpException($exception->getMessage(), $exception);
        }

        // Handle 404 errors
        if ($exception instanceof NotFoundHttpException) {
            $response = new Response(
                $this->twig->render('error/404.html.twig', [
                    'message' => $exception->getMessage(),
                ]),
                Response::HTTP_NOT_FOUND
            );
            $event->setResponse($response);

            return;
        }

        // Handle other HTTP exceptions
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $template = $statusCode >= 500 ? 'error/500.html.twig' : 'error/404.html.twig';

            $response = new Response(
                $this->twig->render($template),
                $statusCode
            );
            $event->setResponse($response);
        }
    }

    private function isHtmlRequest(?string $format): bool
    {
        return $format === null || $format === 'html';
    }
}
