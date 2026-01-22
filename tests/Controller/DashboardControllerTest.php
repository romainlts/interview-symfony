<?php

namespace App\Tests\Controller;

use App\Entity\User;
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
}
