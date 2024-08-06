<?php

namespace App\Repository;

use App\Entity\DayLeaveRequest;
use App\Enum\LeaveRequestStateEnum;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DayLeaveRequest>
 */
class DayLeaveRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DayLeaveRequest::class);
    }

    /**
     * @return DayLeaveRequest[] Returns an array of DayLeaveRequest objects
     */
    public function getDayLeaveRequests(DateTime $start, DateTime $end): array
    {
        return $this->createQueryBuilder('dlr')
            ->leftJoin('dlr.leaveRequest', 'lr')
            ->andWhere('lr.state = :accepted')
            ->andWhere('dlr.dayDate >= :start')
            ->andWhere('dlr.dayDate <= :end')
            ->setParameter('accepted', LeaveRequestStateEnum::Accepted->value)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('dlr.dayDate', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    //    public function findOneBySomeField($value): ?DayLeaveRequest
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
