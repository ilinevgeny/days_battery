<?php

declare(strict_types=1);

namespace App\Battery\Presentation\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function index(): Response
    {
        // Authentication is checked client-side via sessionStorage
        // If user is not authenticated, JavaScript will redirect to home
        return $this->render('dashboard/index.html.twig');
    }
}
