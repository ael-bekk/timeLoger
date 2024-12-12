<?php


namespace App\Repository;

use App\Entity\LogTime;
use App\Entity\Session;

class LogTimeRepository
{

    /**
     * @param Session[] $sessions
     */
    private function calcLogTime(array $sessions): int
    {
        $totalLogTime = 0.0;
        foreach ($sessions as $session) {
            $totalLogTime += $session->getTotalHours();
        }
        return (int) round($totalLogTime);
    }

    /**
     * @param Session[] $sessions
     */
    public function getLogTimeFromSessions(array $sessions): LogTime
    {
        if (empty($sessions)) {
            throw new \RuntimeException("Empty \$sessions array passed to getLogTimeFromSessions() method");
        }
        $username = $sessions[0]->getUsername();
        return (new LogTime())->setUsername($username)->setTotalHours($this->calcLogTime($sessions));
    }
}
