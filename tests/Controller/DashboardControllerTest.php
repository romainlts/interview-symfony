<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Beneficiary;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DashboardControllerTest extends WebTestCase
{
    private function loginTestUser(): \Symfony\Bundle\FrameworkBundle\KernelBrowser
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'tester@gmail.com']);

        $this->assertNotNull($testUser, 'Test user not found: ensure fixtures create tester@gmail.com');

        $client->loginUser($testUser);

        return $client;
    }

    public function testDashboardRedirectsWhenNotAuthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseRedirects('/login');
    }

    public function testDashboardLoadsForAuthenticatedUser(): void
    {
        $client = $this->loginTestUser();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();

        // Check for presence of non-persisted beneficiaries list
        $this->assertSelectorExists('.beneficiary-non-persisted');
        $this->assertSelectorCount(12, '.beneficiary-non-persisted');
    }

    public function testApiBeneficiariesEndpointForAuthenticatedUser(): void
    {
        $client = $this->loginTestUser();
        $client->request('GET', '/api/beneficiaries');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            'application/ld+json',
            $client->getResponse()->headers->get('Content-Type')
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('member', $data);
        $this->assertIsArray($data['member']);
    }

    /**
     * Test deletion of a beneficiary from dashboard modal
     * 
     * @return void
     */
    public function testDeleteBeneficiaryFromDashboardModal(): void
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'tester@gmail.com']);

        $this->assertNotNull($testUser, 'Test user not found: ensure fixtures create tester@gmail.com');

        $client->loginUser($testUser);

        // Create a beneficiary to delete
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $beneficiary = new Beneficiary();
        $beneficiary->setName('Test Beneficiary to Delete');
        $beneficiary->setCreatorEmail($testUser->getUserIdentifier());
        $beneficiary->setCreatedAt(new \DateTimeImmutable());

        $em->persist($beneficiary);
        $em->flush();

        $beneficiaryId = $beneficiary->getId();
        $this->assertNotNull($beneficiaryId);

        // Load dashboard and assert modal + form exist
        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('#modal_beneficiary_deletion');
        $this->assertSelectorExists('#beneficiary-delete-form');
        $this->assertSelectorExists('#beneficiary-delete-id');
        $this->assertSelectorExists('#beneficiary-delete-name');

        // Submit the real delete form rendered in the modal (CSRF included in HTML)
        $form = $crawler->selectButton('Confirm')->form([
            'form[id]' => $beneficiaryId,
        ]);

        $client->submit($form);
        $this->assertResponseRedirects();
        
        // Follow redirect back to dashboard
        $client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Check flash message
        $crawler = $client->getCrawler();
        $this->assertSelectorExists('#flash-messages');
        $success = $crawler->filter('#flash-messages')->attr('data-success');
        $this->assertStringContainsString('Beneficiary deleted successfully.', $success);

        $em->clear();
        $deleted = $em->getRepository(Beneficiary::class)->find($beneficiaryId);
        $this->assertNull($deleted, 'Beneficiary should be deleted after submitting dashboard delete form');
    }

    /**
     * Test modification of a beneficiary from dashboard modal
     * 
     * @return void
     */
    public function testUpdateBeneficiaryFromDashboardModal(): void
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'tester@gmail.com']);

        $this->assertNotNull($testUser, 'Test user not found: ensure fixtures create tester@gmail.com');

        $client->loginUser($testUser);

        // Create a beneficiary to update
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $beneficiary = new Beneficiary();
        $beneficiary->setName('Test Beneficiary to Update');
        $beneficiary->setCreatorEmail($testUser->getUserIdentifier());
        $beneficiary->setCreatedAt(new \DateTimeImmutable());

        $em->persist($beneficiary);
        $em->flush();

        $beneficiaryId = $beneficiary->getId();
        $this->assertNotNull($beneficiaryId);

        // Load dashboard and assert modal + form exist
        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('#modal_beneficiary_modification');
        $this->assertSelectorExists('#beneficiary-update-form');
        $this->assertSelectorExists('#beneficiary-update-id');
        $this->assertSelectorExists('#beneficiary-update-name');

        // Submit the real update form rendered in the modal (CSRF included in HTML)
        $form = $crawler->filter('#beneficiary-update-form')->selectButton('Confirm')->form([
            'form[id]' => $beneficiaryId,
            'form[name]' => 'Updated Beneficiary Name',
        ]);

        $client->submit($form);
        $this->assertResponseRedirects();
        
        // Follow redirect back to dashboard
        $client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Check flash message
        $crawler = $client->getCrawler();
        $this->assertSelectorExists('#flash-messages');
        $success = $crawler->filter('#flash-messages')->attr('data-success');
        $this->assertStringContainsString('Beneficiary updated successfully.', $success);

        $em->clear();
        $modifiedBeneficiary = $em->getRepository(Beneficiary::class)->find($beneficiaryId);
        $this->assertSame('Updated Beneficiary Name', $modifiedBeneficiary->getName());

        $em->remove($modifiedBeneficiary);
        $em->flush();
    }
}
