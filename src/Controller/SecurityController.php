<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Form\Security\LoginType;

final class SecurityController extends AbstractController
{
    /**
     * Displays and handles the login form
     *
     * @param AuthenticationUtils $authenticationUtils Provides authentication errors and last username
     * @return Response The rendered login page
     */
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $loginForm = $this->createForm(
            LoginType::class,
            ['username' => $lastUsername],
            ['action' => $this->generateUrl('app_login')]
        );

        return $this->render('security/login.html.twig', [
            'loginForm' => $loginForm,
            'error' => $error,
        ]);
    }
}
