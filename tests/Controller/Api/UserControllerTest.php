<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UserControllerTest extends WebTestCase
{
    public function testListRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/users');
        self::assertResponseStatusCodeSame(401);
    }

    public function testGetMeRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/users/me');
        self::assertResponseStatusCodeSame(401);
    }
}
