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

    /**
     * Handles the deletion of an existing Beneficiary entity
     *
     * @param Request $request The current HTTP request
     * @param EntityManagerInterface $em The entity manager for database operations
     * @return Response A redirect response to the dashboard or referer
     */
    #[Route('/beneficiaries/delete', name: 'beneficiary_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('beneficiary_delete'))
            ->add('id')
            ->getForm();

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('error', 'Invalid form submission.');
            return $this->redirectToRefererOrDashboard($request);
        }

        $id = $form->get('id')->getData();

        if (!$id) {
            $this->addFlash('error', 'Missing beneficiary id.');
            return $this->redirectToRefererOrDashboard($request);
        }

        $beneficiary = $em->getRepository(Beneficiary::class)->find($id);

        if(!$beneficiary) {
            $this->addFlash('error', 'Beneficiary not found.');
            return $this->redirectToRefererOrDashboard($request);
        }

        $em->remove($beneficiary);
        $em->flush();

        $this->addFlash('success', 'Beneficiary deleted successfully.');

        return $this->redirectToRefererOrDashboard($request);
    }

    /**
     * Handles updating an existing Beneficiary entity
     *
     * @param Request $request The current HTTP request
     * @param EntityManagerInterface $em The entity manager for database operations
     * @return Response A redirect response to the dashboard or referer
     */
    #[Route('/beneficiaries/update', name: 'beneficiary_update', methods: ['POST'])]
    public function update(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('beneficiary_update'))
            ->add('id')
            ->add('name')
            ->getForm();

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('error', 'Invalid form submission.');
            return $this->redirectToRefererOrDashboard($request);
        }

        $id = $form->get('id')->getData();
        $name = $form->get('name')->getData();

        if (!$id || !$name) {
            $this->addFlash('error', 'Missing beneficiary id or name.');
            return $this->redirectToRefererOrDashboard($request);
        }

        $beneficiary = $em->getRepository(Beneficiary::class)->find($id);

        if (!$beneficiary) {
            $this->addFlash('error', 'Beneficiary not found.');
            return $this->redirectToRefererOrDashboard($request);
        }

        $beneficiary->setName($name);
        $em->flush();

        $this->addFlash('success', 'Beneficiary updated successfully.');

        return $this->redirectToRefererOrDashboard($request);
    }

    /**
     * Redirects to the referer URL if available, otherwise to the dashboard
     *
     * @param Request $request The current HTTP request
     * @return Response A redirect response
     */
    private function redirectToRefererOrDashboard(Request $request): Response
    {
        $referer = $request->headers->get('referer');

        return $referer
            ? $this->redirect($referer)
            : $this->redirectToRoute('app_dashboard');
    }
}
