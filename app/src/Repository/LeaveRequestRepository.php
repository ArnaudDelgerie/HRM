<?php

namespace App\Repository;

use App\Entity\LeaveRequest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LeaveRequest>
 */
class LeaveRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LeaveRequest::class);
    }

    public function getLeaveRequests(): Query
    {
        return $this->createQueryBuilder('l')
            ->orderBy('l.firstDay', 'DESC')
            ->getQuery();
    }

    public function getLeaveRequestsByUser(User $user): Query
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.user = :user')
            ->setParameter('user', $user->getId())
            ->orderBy('l.firstDay', 'DESC')
            ->getQuery();
    }
}
