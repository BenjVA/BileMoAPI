<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findPublicUsersByCustomer(UserInterface $customer): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select(
                'u.id',
                'u.email',
                'u.firstName',
                'u.lastName',
                'u.phoneNumber',
            )
            ->from('App:User', 'u')
            ->where('u.customer = :customer')
            ->setParameter('customer', $customer)
        ;

        return $qb->getQuery()->getResult();
    }
}
