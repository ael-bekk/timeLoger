<?php


namespace App\Tests\Controller\Session;

use App\Entity\Session;
use App\Repository\SessionRepository;
use App\Tests\Controller\AbstractApiTest;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class CreateSessionTest extends AbstractApiTest
{

    use RefreshDatabaseTrait;

    private const AUTHORIZED_TOKEN = 'session_write';

    public function testCantCreateSessionWhileUnauthenticated(): void
    {
        $this->client->request(
            'POST',
            '/api/sessions',
            [
                'json' => [
                    "uuid" => Uuid::v4(),
                    "username" => "spoody",
                    "hostname" => "bocal-imac",
                    "startedAt" => "2020-01-13T12:02:19",
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @dataProvider wrongTokensDataProvider
     */
    public function testCantCreateSessionWithInvalidToken(string $token): void
    {
        $this->client->request(
            'POST',
            '/api/sessions',
            [
                'json' => [
                    "uuid" => Uuid::v4(),
                    "username" => "spoody",
                    "hostname" => "bocal-imac",
                    "startedAt" => "2020-01-13T12:02:19",
                ],
                'headers' => [
                    'Authorization' => $token,
                ]
            ]
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCanCreateSessionWithValidToken(): void
    {
        $uuid = Uuid::v4();
        $this->client->request(
            'POST',
            '/api/sessions',
            [
                'json' => [
                    "uuid" => $uuid,
                    "username" => "spoody",
                    "hostname" => "bocal-imac",
                    "startedAt" => "2020-01-13T12:02:19",
                ],
                'headers' => [
                    'Authorization' => self::AUTHORIZED_TOKEN,
                ]
            ]
        );
        $this->assertResponseIsSuccessful();

        /**
         * @var SessionRepository $sessionRepo
         */
        $sessionRepo = $this->getRepository(Session::class);
        $createdSession = $sessionRepo->findOneBy(['uuid' => $uuid]);

        $this->assertNotNull($createdSession);
    }

    /**
     * @dataProvider invalidInputDataProvider
     */
    public function testCantCreateSession(
        string $field,
        string $value,
        int $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY
    ): void {
        $validData = [
            "uuid" => Uuid::v4(),
            "username" => "spoody",
            "hostname" => "bocal-imac",
            "startedAt" => "2020-01-13T12:02:19",
        ];
        unset($validData[$field]);
        $payload = array_merge($validData, [$field => $value]);
        $this->client->request(
            'POST',
            '/api/sessions',
            [
                'json' => $payload,
                'headers' => [
                    'Authorization' => self::AUTHORIZED_TOKEN,
                ]
            ]
        );
        $this->assertResponseStatusCodeSame($statusCode);
    }

    public function testCantCreateSessionWithUsedUuid(): void
    {
        $this->client->request(
            'POST',
            '/api/sessions',
            [
                'json' => [
                    "uuid" => "3fa85f64-5717-4562-b3fc-2c963f66afa6",
                    "username" => "spoody",
                    "hostname" => "bocal-imac",
                    "startedAt" => "2020-01-13T12:02:19",
                ],
                'headers' => [
                    'Authorization' => self::AUTHORIZED_TOKEN,
                ]
            ]
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    /**
     * @return array<string, array<int, int|string>>
     */
    public function invalidInputDataProvider(): array
    {
        return [
            'invalid uuid' => ['uuid', 'invalid_uuid', Response::HTTP_BAD_REQUEST],
            'empty uuid' => ['uuid', '', Response::HTTP_BAD_REQUEST],
            'empty username' => ['username', ''],
            'empty hostname' => ['hostname', ''],
            'invalid start date' => ['startedAt', '2020-30-30 24:00:20', Response::HTTP_BAD_REQUEST],
            'empty start date' => ['startedAt', '', Response::HTTP_BAD_REQUEST],
            'invalid end date' => ['endedAt', '2020-30-30 24:00:20', Response::HTTP_BAD_REQUEST],
            'empty end date' => ['endedAt', '', Response::HTTP_BAD_REQUEST],
//            'end date before start date' => ['endedAt', '2020-01-12T12:02:19', Response::HTTP_UNPROCESSABLE_ENTITY],
        ];
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
