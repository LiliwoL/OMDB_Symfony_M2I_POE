<?php

namespace App\Repository;

use App\Entity\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Vote|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vote|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vote[]    findAll()
 * @method Vote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    /**
     * Renvoi de la moyenne
     *
     * @param string $imdbID
     */
    public function getAverageOLDSCHOOL( string $imdbID )
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            "SELECT AVG(v.note) AS moyenne FROM App\Entity\Vote v " .
            "WHERE v.imdbID = :imdbID"
        )->setParameter('imdbID', $imdbID);


        // DEbug du SQL généré
        //dump ( $query->getSQL() );
        //die;

        // Exécution de la requête
        $result = $query->execute();

        //dump ( $result );
        //die;

        // Renvoie juste la première case du tableau que l'on arrondit au chiffre supérieur avec round()
        return round( $result[0]['moyenne'] );
    }

    // /**
    //  * @return Vote[] Returns an array of Vote objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Vote
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
