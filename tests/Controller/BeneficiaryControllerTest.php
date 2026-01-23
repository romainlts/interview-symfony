<?php

namespace App\Tests\Controller;

use App\Entity\Beneficiary;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BeneficiaryControllerTest extends WebTestCase
{
    public function testCreateBeneficiaryFromDashboard(): void
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'tester@gmail.com']);

        $this->assertNotNull($testUser, 'Test user not found: ensure fixtures create tester@gmail.com');

        $client->loginUser($testUser);

        $router = static::getContainer()->get('router');
        $dashboardUrl = $router->generate('app_dashboard');

        $crawler = $client->request('GET', $dashboardUrl);

        $name = 'Test Beneficiary '.uniqid();

        $form = $crawler->selectButton('Submit')->form([
            'beneficiary[name]' => $name,
        ]);

        $client->submit($form);
        $this->assertResponseRedirects($dashboardUrl);

        $client->followRedirect();
        $this->assertResponseIsSuccessful();

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $beneficiary = $em->getRepository(Beneficiary::class)->findOneBy([
            'name' => $name,
            'creatorEmail' => $testUser->getUserIdentifier(),
        ]);

        $this->assertNotNull($beneficiary);
        $this->assertNotNull($beneficiary->getCreatedAt());
    }

    /**
     * Test deletion of a beneficiary
     * 
     * @return void
     */
    public function testDeleteBeneficiary(): void
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

        $router = static::getContainer()->get('router');

        // Get CSRF token from the dashboard page
        $crawler = $client->request('GET', $router->generate('app_dashboard'));
        $csrfToken = $crawler->filter('#beneficiary-delete-form input[name="form[_token]"]')->attr('value');

        $client->request('POST', $router->generate('beneficiary_delete'), [
            'form' => [
                'id' => $beneficiaryId,
                '_token' => $csrfToken,
            ],
        ]);

        $this->assertResponseRedirects();

        $em->clear();
        $deletedBeneficiary = $em->getRepository(Beneficiary::class)->find($beneficiaryId);
        $this->assertNull($deletedBeneficiary, 'Beneficiary should be deleted');
    }
}
