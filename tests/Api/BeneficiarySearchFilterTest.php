<?php

namespace App\Tests\Api;

use App\Entity\User;
use App\Entity\Beneficiary;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BeneficiarySearchFilterTest extends WebTestCase
{
    public function testFilterBeneficiariesByName(): void
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'tester@gmail.com']);

        $this->assertNotNull($testUser, 'Test user not found: ensure fixtures create tester@gmail.com');

        $client->loginUser($testUser);

        // Create test beneficiaries 
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $b1 = new Beneficiary();
        $filterName = uniqid();
        $b1->setName($filterName);
        $b1->setCreatorEmail('tester@gmail.com');
        $b1->setCreatedAt(new \DateTimeImmutable());

        $b2 = new Beneficiary();
        $b2->setName('Bob Builder');
        $b2->setCreatorEmail('tester@gmail.com');
        $b2->setCreatedAt(new \DateTimeImmutable());

        $em->persist($b1);
        $em->persist($b2);
        $em->flush();

        // Call API with filter
        $client->request('GET', '/api/beneficiaries?name=' . urlencode($filterName), server: [
            'HTTP_ACCEPT' => 'application/ld+json',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            'application/ld+json',
            $client->getResponse()->headers->get('Content-Type')
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('member', $data);
        $this->assertIsArray($data['member']);

        // We expect Alice to be returned, Bob not.
        $names = array_map(static fn (array $item) => $item['name'] ?? null, $data['member']);

        $this->assertContains($filterName, $names);
        $this->assertNotContains('Bob Builder', $names);

        $em->remove($b1);
        $em->remove($b2);  
        $em->flush();
    }
}
