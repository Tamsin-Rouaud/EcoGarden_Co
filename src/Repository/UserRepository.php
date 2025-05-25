<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * Repository personnalisé pour l'entité User.
 *
 * Implémente également PasswordUpgraderInterface afin de permettre
 * la mise à jour automatique des mots de passe hachés si l'algorithme change.
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Méthode utilisée automatiquement par Symfony pour mettre à jour
     * le hash du mot de passe d’un utilisateur lorsque nécessaire (ex : changement d’algo).
     *
     * @param PasswordAuthenticatedUserInterface $user L'utilisateur à mettre à jour
     * @param string $newHashedPassword Le nouveau mot de passe haché
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            // Si ce n’est pas une instance de notre entité User, on lève une exception
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        // Mise à jour du mot de passe dans l'entité
        $user->setPassword($newHashedPassword);
        // Persistance et enregistrement immédiat en base de données
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }


}
