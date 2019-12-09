<?php

namespace App\Service;


use App\Entity\Vote;

class ControlMailSender
{
    private $mailer;

    // Récupération du service Mailer à la construction
    public function __construct( \Swift_Mailer $mailer )
    {
        $this->mailer = $mailer;
    }

    public function success( Vote $vote )
    {
       // 1. Création du message
       $message = new \Swift_Message();

       $message->setFrom ( 'no-reply@omdb.com' );
       $message->setTo ( "administrateurdusite@omdb.com" );
       $message->setSubject( "SUCCESS - Un vote vient d'être ajouté au site" );
       $message->setBody (
           "Un nouveau vote a été ajouté. \n" .
           "ImdbID: " . $vote->getImdbID() . "\n" .
           "Note: " . $vote->getNote()
       );

       // 2. Envoi de l'email
       $result = $this->mailer->send($message);

       return $result;
    }

    public function error( Vote $vote )
    {
        // 1. Création du message
        $message = new \Swift_Message();

        $message->setFrom ( 'no-reply@omdb.com' );
        $message->setTo ( "administrateurdusite@omdb.com" );
        $message->setSubject( "ERROR - Un problème a été rencontré" );
        $message->setBody (
            "Une erreur a été rencontrée"
        );

        // 2. Envoi de l'email
        $result = $this->mailer->send($message);

        return $result;
    }
}