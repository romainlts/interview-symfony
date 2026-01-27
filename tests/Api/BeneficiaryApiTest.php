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

    // Test deletion of beneficiary via API
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
        $beneficiary->setName('Test Beneficiary to Delete' . uniqid());
        $beneficiary->setCreatorEmail($testUser->getUserIdentifier());
        $beneficiary->setCreatedAt(new \DateTimeImmutable());

        $em->persist($beneficiary);
        $em->flush();

        $id = $beneficiary->getId();

        // Call API to delete beneficiary
        $client->request('DELETE', '/api/beneficiaries/' . $id, server: [
            'HTTP_ACCEPT' => 'application/ld+json'
        ]);

        $this->assertResponseStatusCodeSame(204);
        $this->assertSame('', (string) $client->getResponse()->getContent());

        $em->clear();
        $deleted = $em->getRepository(Beneficiary::class)->find($id);
        $this->assertNull($deleted, 'Beneficiary should be deleted from database');
    }
    
    // Test modification of beneficiary via API
    public function testUpdateBeneficiary(): void
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'tester@gmail.com']);

        $this->assertNotNull($testUser, 'Test user not found: ensure fixtures create tester@gmail.com');

        $client->loginUser($testUser);

        // Create a beneficiary to update
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $beneficiary = new Beneficiary();
        $beneficiary->setName('Test Beneficiary to Update' . uniqid());
        $beneficiary->setCreatorEmail($testUser->getUserIdentifier());
        $beneficiary->setCreatedAt(new \DateTimeImmutable());

        $em->persist($beneficiary);
        $em->flush();

        $id = $beneficiary->getId();

        // Call API to update beneficiary
        $client->request('PATCH', '/api/beneficiaries/' . $id, server: [
            'CONTENT_TYPE' => 'application/merge-patch+json',
            'HTTP_ACCEPT' => 'application/ld+json'
        ], content: json_encode(['name' => 'Updated Beneficiary Name']));

        $this->assertResponseStatusCodeSame(200);

        $em->clear();
        $updatedBeneficiary = $em->getRepository(Beneficiary::class)->find($id);
        $this->assertNotNull($updatedBeneficiary);
        $this->assertSame('Updated Beneficiary Name', $updatedBeneficiary->getName());

        $em->remove($updatedBeneficiary);
        $em->flush();
    }
}
