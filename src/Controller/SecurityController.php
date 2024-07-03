<?php

/**
 * Security controller.
 */

namespace App\Controller;

use App\Service\SecurityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SecurityController.
 */
class SecurityController extends AbstractController
{
    private SecurityService $securityService;

    /**
     * Constructor.
     *
     * @param SecurityService $securityService Security service
     */
    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Displays the login form and handles authentication errors.
     *
     * @return Response HTTP response
     */
    #[Route(path: '/login', name: 'security_login')]
    public function login(): Response
    {
        $error = $this->securityService->getLastAuthenticationError();
        $lastUsername = $this->securityService->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * Logout action (no logic needed).
     *
     * @throws \LogicException This method can be blank - it will be intercepted by the logout key on your firewall
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
