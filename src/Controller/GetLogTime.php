<?php


namespace App\Controller;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\LogTime;
use App\Repository\LogTimeRepository;
use App\Repository\SessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;

class GetLogTime
{

    private const MAX_DAYS = 80;

    public function __construct(
        private SessionRepository $sessionRepository,
        private LogTimeRepository $logTimeRepository,
    ) {
    }

    /**
     * @return ArrayCollection<int, LogTime>
     */
    public function __invoke(Request $request): Collection
    {
        if (!$request->query->has('start_date') || !$request->query->has('end_date')) {
            throw new InvalidArgumentException("start_date and end_date fields are required");
        }

        $username = (string) $request->query->get('username');
        if (empty($username)) {
            $username = null;
        }

        $startDate = \DateTime::createFromFormat(
            "Y-m-d",
            (string) $request->query->get('start_date', '')
        );
        if ($startDate === false) {
            throw new InvalidArgumentException("Invalid start_date");
        }
        $startDate->setTime(0, 0);

        $endDate = \DateTime::createFromFormat(
            "Y-m-d",
            (string) $request->query->get('end_date', '')
        );
        if ($endDate === false) {
            throw new InvalidArgumentException("Invalid end_date");
        }
        $endDate->setTime(23, 59, 59);

        // Limit the time span to not exhaust memory
        if ($startDate->diff($endDate)->days > self::MAX_DAYS) {
            throw new InvalidArgumentException("The time span must not exceed ".self::MAX_DAYS." days");
        }

        $sessions = $this->sessionRepository->getSessionsGroupByUsername($startDate, $endDate, $username);
        if (empty($sessions)) {
            return new ArrayCollection();
        }
        $logTimes = [];
        foreach ($sessions as $userSessions) {
            $logTimes[] = $this->logTimeRepository->getLogTimeFromSessions($userSessions);
        }
        return new ArrayCollection($logTimes);
    }
}
