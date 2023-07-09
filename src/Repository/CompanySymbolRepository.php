<?php

namespace App\Repository;

use App\Entity\CompanySymbol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompanySymbol>
 *
 * @method CompanySymbol|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanySymbol|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanySymbol[]    findAll()
 * @method CompanySymbol[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanySymbolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanySymbol::class);
    }

    public function save(CompanySymbol $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CompanySymbol $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    function isTableEmpty(): bool
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('count(t.id)');
        $count = $qb->getQuery()->getSingleScalarResult();
        return !$count;
    }
}
