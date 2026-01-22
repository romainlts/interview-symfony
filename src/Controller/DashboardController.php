<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Faker\Generator;

final class DashboardController extends AbstractController
{
    /**
     * Displays the main dashboard page
     * Displays a list of non-persisted beneficiaries with generated avatars
     *
     * @param Generator $faker Faker service for generating fake data
     * @return Response The rendered dashboard template
     */
    #[Route('/', name: 'app_dashboard')]
    public function index(Generator $faker): Response
    {
        // Generate non-persisted beneficiaries with avatars
        // Dicebear avatar API endpoint with parameters for eyes and mouth styles
        $avatarEndpoint = 'https://api.dicebear.com/8.x/avataaars/svg?eyes=hearts,happy,default,side,wink&mouth=smile,default,twinkle,serious&seed=';
        $nonPersistedBeneficiaries = [];

        for ($i = 0; $i < 12; $i++) {
            $firstName = $faker->firstName();
            $nonPersistedBeneficiaries[] = [
                'firstName' => $firstName,
                'avatarUrl' => $avatarEndpoint . urlencode($firstName),
            ];
        }

        return $this->render('dashboard.html.twig', [
            'avatarEndpoint' => $avatarEndpoint,
            'nonPersistedBeneficiaries' => $nonPersistedBeneficiaries,
        ]);
    }
}
