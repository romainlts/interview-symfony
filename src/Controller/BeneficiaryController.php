<?php

namespace App\Controller;

use App\Entity\Beneficiary;
use App\Form\Entity\BeneficiaryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class BeneficiaryController extends AbstractController
{
    /**
     * Handles the creation of a new Beneficiary entity
     *
     * @param Request $request The current HTTP request
     * @param EntityManagerInterface $em The entity manager for database operations
     * @return Response A redirect response to the dashboard or referer
     */
    #[Route('/beneficiaries/create', name: 'beneficiary_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $beneficiary = new Beneficiary();
        $form = $this->createForm(BeneficiaryType::class, $beneficiary);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $beneficiary->setCreatorEmail($user->getEmail());
            $beneficiary->setCreatedAt(new \DateTimeImmutable());

            $em->persist($beneficiary);
            $em->flush();
            
            $this->addFlash('success', 'Beneficiary created successfully.');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        $referer = $request->headers->get('referer');

        return $referer
            ? $this->redirect($referer)
            : $this->redirectToRoute('app_dashboard');
    }
}
