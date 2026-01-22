<?php

namespace App\Tests\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiAuthenticationEntryPointTest extends WebTestCase
{
    public function testApiReturnsJson401WhenNotAuthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/beneficiaries');

        $this->assertResponseStatusCodeSame(401);
        $this->assertResponseFormatSame('json');

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertSame('Authentication required', $data['error']);
        $this->assertSame('You must be authenticated to access this resource', $data['message']);
    }

    public function testApiDoesNotRedirectToLoginPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/beneficiaries');

        $this->assertResponseStatusCodeSame(401);
        $this->assertFalse($client->getResponse()->isRedirection());
    }

    public function testApiWorksWhenAuthenticated(): void
    {
        $client = static::createClient();

        // Créer et connecter un utilisateur
        $userRepository = static::getContainer()->get('doctrine')->getRepository(\App\Entity\User::class);
        $testUser = $userRepository->findOneBy(['email' => 'tester@gmail.com']);

        $this->assertNotNull($testUser, 'Test user not found: ensure fixtures create tester@gmail.com');

        $client->loginUser($testUser);

        // Accès à l'API authentifié
        $client->request('GET', '/api/beneficiaries');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('application/ld+json', $client->getResponse()->headers->get('Content-Type'));
    }

    public function testMultipleApiEndpointsReturnJsonWhenNotAuthenticated(): void
    {
        $client = static::createClient();

        $endpoints = [
            '/api/beneficiaries',
            '/api/users',
        ];

        foreach ($endpoints as $endpoint) {
            $client->request('GET', $endpoint);

            $this->assertResponseStatusCodeSame(401, "Failed for endpoint: $endpoint");
            $this->assertStringContainsString('application/json', $client->getResponse()->headers->get('Content-Type'));

            $data = json_decode($client->getResponse()->getContent(), true);
            $this->assertArrayHasKey('error', $data);
        }
    }
}
