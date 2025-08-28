<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RegisterControllerTest extends WebTestCase
{
    public function testRegisterValidationFailure(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['email' => 'bad']));

        self::assertResponseStatusCodeSame(400);
    }

    public function testRegisterSuccess(): void
    {
        $client = static::createClient();
        $payload = ['email' => 'user@example.test', 'password' => 'secret123'];
        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        self::assertTrue(in_array($client->getResponse()->getStatusCode(), [201, 409]));
    }
}
