<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;


class TempController extends AbstractController
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    #[Route('/back', name: 'app_tempback')]
    public function index(): Response
    {
        // Récupérer l'email de la session
        $sessionValue = $this->session->get('email');

        if ($sessionValue) {
            return $this->render('basedb.html.twig', [
                'controller_name' => 'TempController',
                'sessionMail' => $sessionValue,
            ]);

        } 
            else {
                return $this->redirectToRoute('app_login');
        }
    }
}
