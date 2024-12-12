<?php

namespace App\Tests\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\DataPersister\SessionDataPersister;
use App\Entity\Session;
use App\Repository\SessionRepository;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;

class SessionDataPersisterTest extends TestCase
{
    private SessionDataPersister $dataPersister;

    /**
     * @var Session[]
     */
    private array $overlappingSessions = [];

    protected function setUp(): void
    {
        $persister = $this->createMock(ContextAwareDataPersisterInterface::class);
        $persister->method('persist')->willReturnCallback(function ($data, $context) {
            return $data;
        });
        $sessionRepo = $this->createMock(SessionRepository::class);
        $sessionRepo->method('getOverlappingSessions')
            ->willReturnCallback(function () {
                return $this->overlappingSessions;
            });

        $this->dataPersister = new SessionDataPersister($persister, $sessionRepo);
    }

    /**
     * @param array<string, mixed> $data
     * @param Session[] $sessions
     * @param array<string, mixed> $expectedData
     * @dataProvider persistDataProvider
     */
    public function testPersist(array $data, array $sessions, array $expectedData): void
    {
        $this->overlappingSessions = $sessions;

        $newSessionEndDate = null;

        $newSessionStartDate = \DateTimeImmutable::createFromMutable(new \DateTime($data['startedAt']));
        if ($data['endedAt'] !== null) {
            $newSessionEndDate = \DateTimeImmutable::createFromMutable(new \DateTime($data['endedAt']));
        }

        $newSession = new Session();
        $newSession->setStartedAt($newSessionStartDate)
            ->setEndedAt($newSessionEndDate);

        /**
         * @var Session $returnedSession
         */
        $returnedSession = $this->dataPersister->persist($newSession, []);

        $this->assertInstanceOf(Session::class, $returnedSession);

        $expectedEndDate = null;

        $expectedStartDate = new \DateTime($expectedData['startedAt']);
        if ($expectedData['endedAt'] !== null) {
            $expectedEndDate = new \DateTime($expectedData['endedAt']);
        }

        $this->assertEquals($expectedStartDate, $returnedSession->getStartedAt());
        $this->assertEquals($expectedEndDate, $returnedSession->getEndedAt());
    }

    /**
     * @return array<string, mixed>
     */
    public function persistDataProvider(): array
    {
        $sessionA = new Session();
        $sessionA->setStartedAt(new \DateTimeImmutable('2021-11-23 9:00'))
            ->setEndedAt(new \DateTimeImmutable('2021-11-23 12:00'));

        $sessionB = new Session();
        $sessionB->setStartedAt(new \DateTimeImmutable('2021-11-23 10:00'))
            ->setEndedAt(new \DateTimeImmutable('2021-11-23 13:00'));
        return [
            'no overlap' => [
                [
                    'startedAt' => '2021-11-23 10:00',
                    'endedAt' => '2021-11-23 11:00',
                ],
                [],
                [
                    'startedAt' => '2021-11-23T10:00:00+00:00',
                    'endedAt' => '2021-11-23T11:00:00+00:00',
                ]
            ],
            'no end date' => [
                [
                    'startedAt' => '2021-11-23 10:00',
                    'endedAt' => null,
                ],
                [],
                [
                    'startedAt' => '2021-11-23 10:00',
                    'endedAt' => null,
                ]
            ],
            'session inside other one' => [
                [
                    'startedAt' => '2021-11-23 10:00',
                    'endedAt' => '2021-11-23 11:00',
                ],
                [
                    $sessionA
                ],
                [
                    'startedAt' => '2021-11-23T10:00:00+00:00',
                    'endedAt' => '2021-11-23T10:00:00+00:00',
                ]
            ],
            'start date before session start and end inside session' => [
                [
                    'startedAt' => '2021-11-23 8:00',
                    'endedAt' => '2021-11-23 11:00',
                ],
                [
                    $sessionA
                ],
                [
                    'startedAt' => '2021-11-23T08:00:00+00:00',
                    'endedAt' => '2021-11-23T09:00:00+00:00',
                ]
            ],
            'start date same session start and end after session' => [
                [
                    'startedAt' =>  $sessionA->getStartedAt()?->format(DateTimeInterface::ATOM),
                    'endedAt' => '2021-11-23 13:00',
                ],
                [
                    $sessionA
                ],
                [
                    'startedAt' => $sessionA->getEndedAt()?->format(DateTimeInterface::ATOM),
                    'endedAt' => '2021-11-23T13:00:00+00:00',
                ]
            ],
            'duplicate session' => [
                [
                    'startedAt' => $sessionA->getStartedAt()?->format(DateTimeInterface::ATOM),
                    'endedAt' => $sessionA->getEndedAt()?->format(DateTimeInterface::ATOM),
                ],
                [
                    $sessionA
                ],
                [
                    'startedAt' => $sessionA->getStartedAt()?->format(DateTimeInterface::ATOM),
                    'endedAt' => $sessionA->getStartedAt()?->format(DateTimeInterface::ATOM),
                ]
            ],
            'multiple overlaps' => [
                [
                    'startedAt' => '2021-11-23 9:30',
                    'endedAt' => '2021-11-23 12:30',
                ],
                [
                    $sessionA,
                    $sessionB,
                ],
                [
                    'startedAt' => '2021-11-23T12:00:00+00:00',
                    'endedAt' => '2021-11-23T12:00:00+00:00',
                ],
            ],
        ];
    }
}
