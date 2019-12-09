<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MovieController extends AbstractController
{
    // Attribut contenant une liste de films
    private $movieList = [
       [
             "id" => 1,
             "title" => "Grind",
             "synopsis" => "␢",
             "genre" => "Action|Comedy",
             "poster" => "http://dummyimage.com/155x163.jpg/5fa2dd/ffffff"
          ],
       [
                "id" => 2,
                "title" => "Ju-on: The Grudge 2",
                "synopsis" => 'Tout le monde dit "I love You"',
                "genre" => "Horror",
                "poster" => "http://dummyimage.com/117x125.png/ff4444/ffffff"
             ],
       [
                   "id" => 3,
                   "title" => "Buck",
                   "synopsis" => "1/0",
                   "genre" => "Documentary",
                   "poster" => "http://dummyimage.com/165x207.png/cc0000/ffffff"
                ],
       [
                      "id" => 4,
                      "title" => "Ultramarathon Man: 50 Marathons, 50 States, 50 Days",
                      "synopsis" => "0.00",
                      "genre" => "Documentary",
                      "poster" => "http://dummyimage.com/189x224.jpg/dddddd/000000"
                   ],
       [
                         "id" => 5,
                         "title" => "On the Ropes",
                         "synopsis" => "-1E+02",
                         "genre" => "Documentary|Drama",
                         "poster" => "http://dummyimage.com/120x123.jpg/5fa2dd/ffffff"
                      ]
    ];


    /**
     * @Route("/movie", name="movie")
     */
    public function index()
    {
        // Création d'une réponse
        $response = new Response();

        // Création d'une variable à retourner
        $output = print_r ( $this->movieList, true);

        // Ajout de la variable à la réponse
        $response->setContent( $output );

        return $response;
    }

    /**
     * @Route(
     *     "/movieTwig",
     *     name="movie en Twig"
     * )
     */
    public function indexTwig()
    {
        // Renvoi de la liste des films
        return $this->render(
            // Chemin du template à rendre
            'movie/index.html.twig',

            // Liste des paramètres à envoyer à la vue
            array(
                'liste' => $this->movieList
            )
        );
    }

    /**
     * @Route(
     *     "/movie/{id}",
     *     name="delete movie with a param",
     *     requirements={
     *      "id"="\d+"
     *     },
     *     methods={
     *      "DELETE"
     *     }
     * )
     * @param int $id
     * @return Response
     */
    public function deleteMovie ( int $id )
    {
        // Suppression de la case $id
        array_splice( $this->movieList , $id , 1);

        // Affichage
        $output = "On supprime le film ayant l'id " . $id;

        // Création d'une variable à retourner
        $output .= print_r ( $this->movieList, true);

        $response = new Response();

        // Ajout de la variable à la réponse
        $response->setContent( $output );

        return $response;
    }

    /**
     * @Route(
     *     "/movie/{id}/{title}",
     *     name="Update movie title with a param",
     *     requirements={
     *      "id"="\d+"
     *     },
     *     methods={
     *      "POST"
     *     }
     * )
     * @param int $id
     * @param string $title
     * @return Response
     * @TODO: Gestion d'un title long avec des espaces etc...
     * @TODO: Au cas où l'id n'existe pas
     */
    public function changeTitleMovie ( int $id, string $title )
    {
        // Mise à jour
        $this->movieList[ $id ][ 'title' ] = $title;

        // Affichage
        $output = "On met à jour le titre du film ayant l'id " . $id;

        // Création d'une variable à retourner
        $output .= print_r ( $this->movieList, true );

        $response = new Response();

        // Ajout de la variable à la réponse
        $response->setContent( $output );

        return $response;
    }

    /**
     * @Route(
     *     "/movie/{id}",
     *     name="movie with a param",
     *     requirements={
     *      "id"="\d+"
     *     }
     * )
     * @return Response
     * @TODO: Gestion de l'id trop grand
     */
    public function getMovie( int $id = 1 )
    {
        // Création d'une réponse
        $response = new Response();

        // Création d'une variable à retourner
        $output = "<h1>Voici le film ayant l'id: " . $id . "</h1>";

        // On ajoute à cette variable le film ayant l'id passé en paramètre
        $output .= print_r ( $this->movieList[ $id ] , true);

        // Ajout de la variable à la réponse
        $response->setContent( $output );

        return $response;
    }

    /**
     * @Route(
     *     "/movieTwig/{id}",
     *     name="movie Twig with a param",
     *     requirements={
     *      "id"="\d+"
     *     }
     * )
     * @return Response
     * @TODO: Gestion de l'id trop grand
     * #TODO: Gestion de l'id correspondant à la réalité du tableau
     */
    public function getMovieTwig( int $id )
    {
        // Renvoi un rendu de template
        return $this->render(
            // Le fichier template
            'movie/movie.html.twig',
            // Les paramètres à envoyer à la vue
            array(
                'movie' => $this->movieList[ $id -1 ]
            )
        );
    }

}
