<?php

namespace App\Repository;

use App\Entity\Advice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository dédié à l'entité Advice (conseils jardinage).
 * Fournit des méthodes personnalisées pour interroger la base de données selon des critères métier.
 */


class AdviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advice::class);
    }


    /**
     * Retourne les conseils liés à un mois donné.
     * Cette méthode interroge directement la base via une requête SQL native.
     * Elle utilise JSON_CONTAINS car le champ `month` est stocké au format JSON.
     *
     * @param int $month Le mois à rechercher (1 = janvier, ..., 12 = décembre)
     * @return array Tableau associatif contenant les résultats
     */
public function findByMonth(int $month): array
{
    $conn = $this->getEntityManager()->getConnection();

    $sql = 'SELECT * FROM advice WHERE JSON_CONTAINS(month, :month_json)';
    $stmt = $conn->prepare($sql);
    $resultSet = $stmt->executeQuery(['month_json' => json_encode([$month])]);

    return $resultSet->fetchAllAssociative();
}


    /**
     * Variante possible pour récupérer les conseils d’un mois en précisant le chemin JSON ("$").
     * Actuellement non utilisée dans le projet, elle pourrait être utile pour affiner ou corriger
     * certains comportements selon la configuration SQL.
     *
     * @param int $month
     * @return array
     */

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
