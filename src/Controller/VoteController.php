<?php

namespace App\Controller;

use App\Service\ControlMailSender;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Vote;

class VoteController extends AbstractController
{
    /**
     *
     * Affichage de la liste de TOUS les votes
     *
     * @Route(
     *     "/vote",
     *     name="vote"
     * )
     */
    public function index()
    {
        // Récupération le repository de la classe Vote
        $voteRepository = $this->getDoctrine()->getRepository( Vote::class );

        // Récupération de TOUS les votes
        $listeVotes = $voteRepository->findAll();


        return $this->render(
            'vote/index.html.twig',
            [
                'listeVotes' => $listeVotes
            ]
        );
    }


    /**
     * @Route(
     *      "/voteByForm",
     *      name="Vote By Form",
     *      methods={
     *          "POST"
     *      }
     * )
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addVoteByForm(
        Request $request,
        EntityManagerInterface $entityManager,
        ControlMailSender $controlMailSender,
        LoggerInterface $logger
    )
    {
        // 1. Nouvelle instance de l'entité VOTE
            $nouveauVote = new Vote();

            // Récupération des paramètres passés dans la requête
            $imdbID = $request->request->get('imdbID');
            $valeurDuVote = $request->request->get('note');

            // Affectation des valeurs à l'instance VOTE
            $nouveauVote->setImdbID( $imdbID );
            $nouveauVote->setNote( $valeurDuVote );


        // 2. Persistance de l'instance de vote dans la base de données
            $entityManager->persist( $nouveauVote );

            // Flush est la méthode qui va réellement appliquer les modifications en base
            $entityManager->flush();

        // 2bis. Envoi d'un mail de contrôle à l'administrateur du site
        // On utilise ici un service créé dans App\Service
            $controlMailSender->success( $nouveauVote );


        // 2ter. Ajout dans le log
            $logger->info( "NOUVEAU VOTE" );


        // 3. Ajouter un message flash
            $this->addFlash( "success", "Votre vote a été pris en compte. Merci!");

        // 3. Redirection vers la fiche du film
            return $this->redirectToRoute(
                'Fiche dun film',
                array(
                    'imdbID' => $imdbID
                )
            );
    }
}
