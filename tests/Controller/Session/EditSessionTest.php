<?php


namespace App\Tests\Controller\Session;

use App\Tests\Controller\AbstractApiTest;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

class EditSessionTest extends AbstractApiTest
{
    use RefreshDatabaseTrait;

    private const AUTHORIZED_TOKEN = 'session_write';
    private const SESSION_UUID = '3fa85f64-5717-4562-b3fc-2c963f66afa6';

    public function testCantEditSessionWhileUnauthenticated(): void
    {
        $this->client->request(
            'PUT',
            '/api/sessions/'.self::SESSION_UUID,
            [
                'json' => [
                    "endedAt" => "2020-01-13T12:02:19",
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @dataProvider wrongTokensDataProvider
     */
    public function testCantEditSessionWithInvalidToken(string $token): void
    {
        $this->client->request(
            'PUT',
            '/api/sessions/'.self::SESSION_UUID,
            [
                'json' => [
                    "endedAt" => "2020-01-13T12:02:19",
                ],
                'headers' => [
                    'Authorization' => $token,
                ]
            ]
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCanEditSessionWithValidToken(): void
    {
        $this->client->request(
            'PUT',
            '/api/sessions/'.self::SESSION_UUID,
            [
                'json' => [
                    "endedAt" => "2021-12-01T15:00:00",
                ],
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
            'session read token' => ['session_read'],
        ];
    }
}
