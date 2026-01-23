<?php

namespace App\Controller;

use App\Entity\Beneficiary;
use App\Form\Entity\BeneficiaryType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Faker\Generator;

final class DashboardController extends AbstractController
{
    /**
     * Displays the main dashboard page
     * Displays a list of non-persisted beneficiaries with generated avatars
     * Displays a list of persisted beneficiaries from the database
     * Renders the form to create a new Beneficiary entity
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

        $beneficiary = new Beneficiary();
        $beneficiary->setName($faker->firstName());
        $beneficiaryForm = $this->createForm(BeneficiaryType::class, $beneficiary, [
            'action' => $this->generateUrl('beneficiary_create')
        ]);

        return $this->render('dashboard.html.twig', [
            'avatarEndpoint' => $avatarEndpoint,
            'nonPersistedBeneficiaries' => $nonPersistedBeneficiaries,
            'beneficiaryForm' => $beneficiaryForm->createView(),
        ]);
    }
}
