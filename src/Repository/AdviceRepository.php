<?php

namespace App\Repository;

use App\Entity\Advice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Advice>
 */
class AdviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advice::class);
    }



public function findByMonth(int $month): array
{
    $conn = $this->getEntityManager()->getConnection();

    $sql = 'SELECT * FROM advice WHERE JSON_CONTAINS(month, :month_json)';
    $stmt = $conn->prepare($sql);
    $resultSet = $stmt->executeQuery(['month_json' => json_encode([$month])]);

    return $resultSet->fetchAllAssociative();
}


// src/Repository/AdviceRepository.php

public function findByCurrentMonth(int $month): array
{
    $conn = $this->getEntityManager()->getConnection();

    $sql = '
        SELECT * FROM advice a
        WHERE JSON_CONTAINS(a.month, :month, "$")
    ';

    $stmt = $conn->prepare($sql);
    $resultSet = $stmt->executeQuery(['month' => json_encode($month)]);

    return $resultSet->fetchAllAssociative();
}


}
