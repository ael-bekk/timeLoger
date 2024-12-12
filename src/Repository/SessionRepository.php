<?php

namespace App\Repository;

use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Session|null find($id, $lockMode = null, $lockVersion = null)
 * @method Session|null findOneBy(array $criteria, array $orderBy = null)
 * @method Session[]    findAll()
 * @method Session[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends ServiceEntityRepository<Session>
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    /**
     * @return Session[]
     */
    public function getSessionsByPeriod(
        \DateTimeInterface $startAt,
        \DateTimeInterface $endAt,
        string $username = null,
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.startedAt >= :startAt')
            ->setParameter('startAt', $startAt)
            ->andWhere('s.endedAt <= :endAt')->setParameter('endAt', $endAt);
        if ($username !== null) {
            $qb->andWhere('s.username = :username')->setParameter('username', $username);
        }
        return $qb->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, Session[]>
     */
    public function getSessionsGroupByUsername(
        \DateTimeInterface $startAt,
        \DateTimeInterface $endAt,
        string $username = null,
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.startedAt >= :startAt')
            ->andWhere('s.startedAt <= :endAt')
            ->setParameters([
                'startAt' => $startAt,
                'endAt' => $endAt,
            ]);
        if ($username !== null) {
            $qb->andWhere('s.username = :username')->setParameter('username', $username);
        }
        $allSessions = $qb->getQuery()->getResult();
        $sessions = [];
        foreach ($allSessions as $session) {
            $sessions[$session->getUsername()][] = $session;
        }
        return $sessions;
    }

    /**
     * @return Session[]
     */
    public function getOverlappingSessions(Session $session): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere(':start <= s.endedAt')
            ->andWhere('s.startedAt <= :end')
            ->andWhere('s.username = :username')
            ->setParameter('start', $session->getStartedAt())
            ->setParameter('end', $session->getEndedAt())
            ->setParameter('username', $session->getUsername())
            ->orderBy('s.startedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Session[] Returns an array of Session objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Session
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
