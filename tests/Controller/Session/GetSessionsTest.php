<?php


namespace App\Tests\Controller\Session;

use App\Tests\Controller\AbstractApiTest;
use Symfony\Component\HttpFoundation\Response;

class GetSessionsTest extends AbstractApiTest
{
    private const AUTHORIZED_TOKEN = 'session_read';

    public function testCantGetSessionsWhileUnauthenticated(): void
    {
        $this->client->request(
            'GET',
            '/api/sessions',
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @dataProvider wrongTokensDataProvider
     */
    public function testCantGetSessionsWithInvalidTokens(string $token): void
    {
        $this->client->request(
            'GET',
            '/api/sessions',
            [
                'headers' => [
                    'Authorization' => $token,
                ]
            ]
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCanGetSessionsWithValidTokens(): void
    {
        $this->client->request(
            'GET',
            '/api/sessions',
            [
                'headers' => [
                    'Authorization' => self::AUTHORIZED_TOKEN,
                ]
            ]
        );
        $this->assertResponseIsSuccessful();
    }

    /**
     * @return string[][]
     */
    public function wrongTokensDataProvider()
    {
        return [
            'no roles token' => ['no_roles'],
            'logtime read token' => ['logtime_read'],
            'session write token' => ['session_write'],
        ];
    }
}
