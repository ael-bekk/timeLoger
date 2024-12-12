<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Session;
use App\Repository\SessionRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class SessionDataPersister implements ContextAwareDataPersisterInterface
{

    public function __construct(
        private ContextAwareDataPersisterInterface $decorated,
        private SessionRepository $sessionRepository,
    ) {
    }

    /**
     * @param mixed $data
     * @param array<string, mixed> $context
     */
    public function supports($data, array $context = []): bool
    {
        return $this->decorated->supports($data, $context);
    }

    private function breakOverlap(Session $newSession): Session
    {
        if ($newSession->getEndedAt() === null) {
            return $newSession;
        }
        /**
         * @var \DateTimeInterface $start
         */
        $start = $newSession->getStartedAt();
        /**
         * @var \DateTimeInterface $end
         */
        $end = $newSession->getEndedAt();

        $sessions = $this->sessionRepository->getOverlappingSessions($newSession);
        if (empty($sessions)) {
            return $newSession;
        }

        foreach ($sessions as $session) {
            /**
             * @var \DateTimeInterface $sessionStartedAt We only select non null from the database
             */
            $sessionStartedAt = $session->getStartedAt();
            /**
             * @var \DateTimeInterface $sessionEndedAt We only select non null from the database
             */
            $sessionEndedAt = $session->getEndedAt();
            // New sess start before old sess
            if ($start < $session->getStartedAt()) {
                $end = clone $sessionStartedAt;
                break;
            }
            // New sess start after/same as old sess

            // New sess end same/before old sess
            if ($end <= $session->getEndedAt()) {
                // Make the start and end the same so this session doesn't count
                $end = clone $start;
                break;
            }

            // New sess ends after old sess
            $start = clone $sessionEndedAt;
        }
        $newSession->setStartedAt(\DateTimeImmutable::createFromInterface($start))
            ->setEndedAt(\DateTimeImmutable::createFromInterface($end));
        return $newSession;
    }

    /**
     * @param mixed $data
     * @param array<string, mixed> $context
     */
    public function persist($data, array $context = []): object
    {
        if ($data instanceof Session) {
            $data = $this->breakOverlap($data);
        }
        /**
         * @var object $decorated
         */
        $decorated = $this->decorated->persist($data, $context);
        return $decorated;
    }

    /**
     * @param mixed $data
     * @param array<string, mixed> $context
     */
    public function remove($data, array $context = []): void
    {
        $this->decorated->remove($data, $context);
    }
}
