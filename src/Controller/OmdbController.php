<?php

namespace App\Controller;

use App\Entity\Goodies;
use App\Entity\Vote;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OmdbController extends AbstractController
{
    // Définition d'un attribut contenant ma clé API
    private $apiKey = "185a318e";


    /**
     * @Route("/omdb", name="Home Page")
     */
    public function index()
    {
        // On cherche à afficher une LISTE de films avec un terme de recherche fixé

        // Définir mon terme de recherche
        $query =  "Running";

        // Création de l'url de recherche
        $url = "http://www.omdbapi.com/?apikey=" . $this->apiKey . "&s=" . $query;


        // Requête sur l'url de recherche en appelant la fonction CallAPI
        $resultats = $this->CallAPI( "GET", $url );

        // Decoder le resultat
        $resultatDecode = json_decode( $resultats, true );


        // $resultatDecode contient 3 cases.
        // On ne renvoie QUE la case qui contient les résultats
        // $resultatDecode['Search']

        // Renvoi du rendu de template avec en paramètre le resultatDecode
        return $this->render(
            'omdb/index.html.twig',
            [
                'liste' => $resultatDecode['Search']
            ]
        );
    }


    /**
     * @Route(
     *     "/omdb/{imdbID}",
     *     name="Fiche dun film"
     * )
     * @return Response
     * @TODO: Trouver la REGEX pour vérifier la validité d'un imdbID
     * @TODO: Afficher la liste des votes dans le TWIG
     */
    public function fiche( $imdbID )
    {
        // Construire l'url de requête
        $url = "http://www.omdbapi.com/?apikey=" . $this->apiKey . "&i=" . $imdbID;
        
        // Appel à la fonction qui fait la requête
        $resultats = $this->CallAPI( "GET", $url );

        // Decoder le resultat
        $resultatDecode = json_decode( $resultats, true );


        // Aller chercher les votes
            // 1. Aller chercher le repository de Vote
            $voteRepository = $this->getDoctrine()->getRepository( Vote::class );

            $moyenne = $voteRepository->getAverageOLDSCHOOL( $imdbID );


        // N'oubliez pas de faire des dump pour savoir ce qu'il y a dans la donnée!
        /*
         * dump ($resultatDecode);
         * die;
         */


        // Aller chercher les goodies
            // 1. Aller chercher le repository de Goodies
            $goodiesRepository = $this->getDoctrine()->getRepository( Goodies::class );

            $listeGoodies = $goodiesRepository->findBy(
                ['imdbID' => $imdbID ]
            );



        // Renvoi du résultat à la vue
        return $this->render(
            'omdb/fiche.html.twig',
            [
                'fiche' => $resultatDecode,
                'moyenne' => $moyenne,
                'goodies' => $listeGoodies
            ]
        );
    }


    /**
     * @Route(
     *     "/omdbsearch",
     *     name="SearchOmdb"
     * )
     * @TODO: Gestion de l'erreur
     */
    public function search( Request $request )
    {
        // On cherche à afficher une LISTE de films avec un terme de recherche donné en requête

        // Définir mon terme de recherche
        $query =  $request->query->get('nomFilm');

        // Création de l'url de recherche
        $url = "http://www.omdbapi.com/?apikey=" . $this->apiKey . "&s=" . $query;


        // Requête sur l'url de recherche en appelant la fonction CallAPI
        $resultats = $this->CallAPI( "GET", $url );

        // Decoder le resultat
        $resultatDecode = json_decode( $resultats, true );


        if ( isset( $resultatDecode['Search'] ) )
        {
            // $resultatDecode contient 3 cases.
            // On ne renvoie QUE la case qui contient les résultats
            // $resultatDecode['Search']

            // Renvoi du rendu de template avec en paramètre le resultatDecode
            return $this->render(
                'omdb/index.html.twig',
                [
                    'liste' => $resultatDecode['Search']
                ]
            );
        }else{
            // On renvoie une erreur

            // Idéalement on crée au prélable une réponse avec le code 404
            $response = new Response();
            $response->setStatusCode( Response::HTTP_NOT_FOUND );

            // On envoie au Twig toutes les infos
            return $this->render(
                'omdb/error.html.twig',
                [
                    'queryTerm' => $query
                ],
                $response
            );
        }

    }


    /**
     * @Route(
     *     "/sendMail",
     *     name="send email",
     *     methods={
     *      "POST"
     *     }
     * )
     */
    public function sendMail( Request $request, \Swift_Mailer $mailer )
    {
        // 1. Récupération des données de la requête POST
        //dump ( $request );
        $imdbID             = $request->request->get( 'imdbID' );
        $emailDestinataire  = $request->request->get( 'emailDestinataire' );

        /*
         * dump ( $imdbID);
         * dump ($emailDestinataire );
         * die;
         */

        // 2. Création de l'email

            // Construire l'url de requête
            $url = "http://www.omdbapi.com/?apikey=" . $this->apiKey . "&i=" . $imdbID;

            // Appel à la fonction qui fait la requête
            $resultats = $this->CallAPI( "GET", $url );

            // Decoder le resultat
            $resultatDecode = json_decode( $resultats, true );


        // Récupérer le rendu d'une vue
        // et on le STOCKE dans une variable $output
        $output = $this->render(
            'mail/partage.html.twig',
            array(
                'movie' => $resultatDecode   // On transmet tout le contenu du JSON
            )
        );

        // Création de l'objet Message
        $message = new \Swift_Message();

        $message->setFrom ( 'm2i@merignac.fr' );
        $message->setTo ( $emailDestinataire );
        $message->setSubject( 'Un utilisateur veut vous partager un film' );
        $message->setBody (
            $output,
            'text/html'
        );

        // 3. Envoi de l'email
        $result = $mailer->send($message);


        // 4. Renvoi d'une réponse ou redirection
        //return new Response("Mail envoyé");


        // Redirection vers la fiche détaillée
        return $this->redirectToRoute(
            'Fiche dun film',
            array(
                'imdbID' => $imdbID
            )
        );
    }


    /**
     * @Route(
     *     "/addVote/{imdbID}/{valeurDuVote}",
     *     name = "Ajouter un vote"
     * )
     *
     * @TODO: gérer les requirements du paramètre
     */
    public function ajouterVote( string $imdbID, int $valeurDuVote, EntityManagerInterface $entityManager)
    {
        // On récupère un vote et un imdbID
        $nouveauVote = new Vote();

        $nouveauVote->setImdbID( $imdbID );
        $nouveauVote->setNote( $valeurDuVote );


        // Persistance de l'instance de vote dans la base de données
        $entityManager->persist( $nouveauVote );

        // Flush est la méthode qui va réellement appliquer les modifications en base
        $entityManager->flush();


        // Avant de faire la redirection
        switch ( $valeurDuVote )
        {
            case 0:
                $this->addFlash( "danger", "Votre vote d'une valeur de zéro a été pris en compte. Merci!");
                break;
            case 5:
                $this->addFlash( "warning", "Votre vote d'une valeur de cinq a été pris en compte. Merci!");
                break;
            case 10:
                $this->addFlash( "success", "Votre vote d'une valeur de dix a été pris en compte. Merci!");
                break;
            default:
                $this->addFlash( "danger", "Bizarre, cette valeur n'est pas prévue");
                break;
        }

        // Redirection vers la fiche du film
        return $this->redirectToRoute(
            'Fiche dun film',
            array(
                'imdbID' => $imdbID
            )
        );
    }

    /**
     * @param $method
     * @param $url
     * @param bool $data
     * @return mixed
     */
    public function CallAPI($method, $url, $data = false)
    {
        $curl = curl_init();

        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "username:password");

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }
}
