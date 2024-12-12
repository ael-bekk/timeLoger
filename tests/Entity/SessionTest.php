<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Session;
use Symfony\Component\Uid\Uuid;

class SessionTest extends TestCase
{
    private Session $session;

    protected function setUp(): void
    {
        $this->session = new Session();
        $this->session->setUuid(Uuid::v4())
            ->setUsername('spoody')
            ->setHostname('bocal-imac')
            ->setStartedAt(new \DateTimeImmutable('2021-12-01 13:37'))
            ->setEndedAt(new \DateTimeImmutable('2021-12-01 15:25'));
    }

    public function testGetTotalHours(): void
    {
        $totalHours = $this->session->getTotalHours();
        $this->assertEquals(1.8, $totalHours);
    }
}
