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
}