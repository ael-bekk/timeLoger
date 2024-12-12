<?php


namespace App\Tests\Controller\LogTime;

use App\Tests\Controller\AbstractApiTest;
use Symfony\Component\HttpFoundation\Response;

class GetLogTimeCollectionTest extends AbstractApiTest
{
    private const AUTHORIZED_TOKEN = 'logtime_read';

    public function testCantGetLogTimeWhileUnauthenticated(): void
    {
        $this->client->request(
            'GET',
            '/api/log_times?start_date=2021-12-01&end_date=2021-12-08',
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @dataProvider wrongTokensDataProvider
     */
    public function testCantGetLogTimeWithInvalidToken(string $token): void
    {
        $this->client->request(
            'GET',
            '/api/log_times?start_date=2021-12-01&end_date=2021-12-08',
            [
                'headers' => [
                    'Authorization' => $token,
                ]
            ]
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCanGetLogTime(): void
    {
        $resp = $this->client->request(
            'GET',
            '/api/log_times?start_date=2021-12-01&end_date=2021-12-08',
            [
                'headers' => [
                    'Authorization' => self::AUTHORIZED_TOKEN,
                ]
            ]
        );
        $resp = $resp->toArray();
        $this->assertResponseIsSuccessful();
        $this->assertCount(2, $resp['hydra:member']);

        $this->assertEquals("spoody", $resp['hydra:member'][0]['username']);
        $this->assertEquals(6, $resp['hydra:member'][0]['totalHours']);

        $this->assertEquals("someone", $resp['hydra:member'][1]['username']);
        $this->assertEquals(1, $resp['hydra:member'][1]['totalHours']);
    }

    public function testTimeSpanIsLimited(): void
    {
        $this->client->request(
            'GET',
            '/api/log_times?start_date=2021-01-01&end_date=2021-04-01',
            [
                'headers' => [
                    'Authorization' => self::AUTHORIZED_TOKEN,
                ]
            ]
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCanGetLogTimeInCsv(): void
    {
        $resp = $this->client->request(
            'GET',
            '/api/log_times?start_date=2021-12-01&end_date=2021-12-08',
            [
                'headers' => [
                    'Authorization' => self::AUTHORIZED_TOKEN,
                    'Accept' => 'text/plain',
                ]
            ]
        );
        $data = $resp->getContent();
        $this->assertResponseIsSuccessful();

        $this->assertEquals("username,totalHours\nspoody,6\nsomeone,1\n", $data);
    }

    public function testCanFilterByUsername(): void
    {
        $resp = $this->client->request(
            'GET',
            '/api/log_times?start_date=2021-12-01&end_date=2021-12-08&username=spoody',
            [
                'headers' => [
                    'Authorization' => self::AUTHORIZED_TOKEN,
                ]
            ]
        );
        $resp = $resp->toArray();
        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $resp['hydra:member']);

        $this->assertEquals("spoody", $resp['hydra:member'][0]['username']);
        $this->assertEquals(6, $resp['hydra:member'][0]['totalHours']);

        $resp = $this->client->request(
            'GET',
            '/api/log_times?start_date=2021-12-01&end_date=2021-12-08&username=non-existing-username',
            [
                'headers' => [
                    'Authorization' => self::AUTHORIZED_TOKEN,
                ]
            ]
        );
        $resp = $resp->toArray();
        $this->assertResponseIsSuccessful();
        $this->assertCount(0, $resp['hydra:member']);
    }

    /**
     * @return string[][]
     */
    public function wrongTokensDataProvider()
    {
        return [
            'no roles token' => ['no_roles'],
            'session read token' => ['session_read'],
            'session write token' => ['session_write'],
        ];
    }
}
