<?php

namespace App\Repository;

use App\Entity\Meeting;
use App\Entity\User;
use App\Enum\MeetingVisibilityEnum;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Meeting>
 */
class MeetingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Meeting::class);
    }

    public function getMeetings(User $user): Query
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.meetingParticipants', 'p')
            ->where(
                new Orx([
                    'm.visibility = :public',
                    new Orx([
                        'p.user = :user',
                        'm.createdBy = :user',
                    ])
                ])
            )
            ->setParameter('public', MeetingVisibilityEnum::Public->value)
            ->setParameter('user', $user->getId())
            ->orderBy('m.startAt', 'DESC')
            ->getQuery();
    }

    public function getMeetingEvents(User $user, DateTime $start, DateTime $end): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.meetingParticipants', 'p')
            ->where(
                new Orx([
                    'm.visibility = :public',
                    new Orx([
                        'p.user = :user',
                        'm.createdBy = :user',
                    ])
                ])
            )
            ->andWhere('m.startAt >= :start')
            ->andWhere('m.endAt <= :end')
            ->andWhere('m.cancelled = 0')
            ->setParameter('public', MeetingVisibilityEnum::Public->value)
            ->setParameter('user', $user->getId())
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('m.startAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
