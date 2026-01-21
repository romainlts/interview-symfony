<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityControllerTest extends WebTestCase
{
    /**
     * Test that the login page loads successfully
     */
    public function testLoginPageLoads(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h5', 'Login to your account');
        $this->assertSelectorExists('input[name="email"]');
        $this->assertSelectorExists('input[name="password"]');
    }

    /**
     * Test authentication with valid credentials
     */
    public function testLoginWithValidCredentials(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Sign in')->form([
            'email' => 'tester@gmail.com',
            'password' => 'I@mTheT€ster',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    /**
     * Test authentication with invalid credentials
     */
    public function testLoginWithInvalidCredentials(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Sign in')->form([
            'email' => 'wrong@gmail.com',
            'password' => 'wrongpassword',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/login');
        $crawler = $client->followRedirect();
        $this->assertStringContainsString(
            'Invalid credentials',
            $crawler->filter('body')->text()
        );
    }

    /**
     * Test logout functionality
     */
    public function testLogout(): void
    {
        $client = static::createClient();

        // First, login
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign in')->form([
            'email' => 'tester@gmail.com',
            'password' => 'I@mTheT€ster',
        ]);
        $client->submit($form);
        $client->followRedirect();

        // Now logout
        $client->request('GET', '/logout');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Test that unauthenticated users cannot access protected pages
     */
    public function testUnauthenticatedUserCannotAccessDashboard(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseRedirects('/login');
    }
}
