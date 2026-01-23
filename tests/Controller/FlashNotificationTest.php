<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FlashNotificationTest extends WebTestCase
{
    public function testFlashMessagesAreRenderedAsDataAttributes(): void
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'tester@gmail.com']);

        $this->assertNotNull($testUser, 'Test user not found: ensure fixtures create tester@gmail.com');

        $client->loginUser($testUser);

        $client->request('GET', '/');

        /** @var SessionInterface $session */
        $session = $client->getRequest()->getSession();
        $session->getFlashBag()->add('success', 'Beneficiary created successfully.');
        $session->getFlashBag()->add('error', 'Validation error.');
        $session->save();

        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#flash-messages');

        $crawler = $client->getCrawler();
        $success = $crawler->filter('#flash-messages')->attr('data-success');
        $error = $crawler->filter('#flash-messages')->attr('data-error');

        $this->assertStringContainsString('Beneficiary created successfully.', $success);
        $this->assertStringContainsString('Validation error.', $error);
    }
}