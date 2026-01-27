<?php

namespace App\Tests\Api;

use App\Entity\User;
use App\Entity\Beneficiary;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BeneficiaryApiTest extends WebTestCase
{
    // Test creation of beneficiaries via API
    public function testCreateBeneficiaries(): void
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'tester@gmail.com']);

        $this->assertNotNull($testUser, 'Test user not found: ensure fixtures create tester@gmail.com');

        $client->loginUser($testUser);

        $name = 'Test Beneficiary ' . uniqid();

        // Call API to create beneficiary
        $client->request('POST', '/api/beneficiaries', server: [
            'CONTENT_TYPE' => 'application/ld+json',
            'HTTP_ACCEPT' => 'application/ld+json'
        ], content: json_encode(['name' => $name]));

        $this->assertResponseStatusCodeSame(201);
        $this->assertStringContainsString(
            'application/ld+json',
            $client->getResponse()->headers->get('Content-Type')
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($name, $data['name']);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $createdBeneficiary = $em->getRepository(Beneficiary::class)->findOneBy(['name' => $name]);
        $this->assertNotNull($createdBeneficiary);

        $em->remove($createdBeneficiary);
        $em->flush();
    }
}
