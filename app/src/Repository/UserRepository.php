<?php

namespace App\Repository;

use App\Entity\User;
use App\Enum\UserStateEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function getUsers(): Query
    {
        return $this
            ->createQueryBuilder('u')
            ->orderBy("CASE WHEN u.state = 'created' THEN 1 WHEN u.state = 'invited' THEN 2 WHEN u.state = 'enabled' THEN 3 WHEN u.state = 'disabled' THEN 4 ELSE 5 END")
            ->addOrderBy('u.id', 'ASC')
            ->getQuery();
    }

    public function getUsersByRole(array $roles, ?array $states = [UserStateEnum::Enabled->value]): Query
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.state in (:states)')
            ->setParameter('states', $states);

        foreach ($roles as $role) {
            $qb
                ->andWhere('u.roles LIKE :role')
                ->setParameter('role', '%' . $role . '%');
        }

        return $qb->getQuery();
    }
}
