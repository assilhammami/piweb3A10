<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/frontoffice/event')]
class EventfrontController extends AbstractController
{
    #[Route('/', name: 'app_frontevent_index', methods: ['GET'])]
    public function index(EventRepository $eventfrontRepository): Response
    {
        return $this->render('event/front.html.twig', [
            'events' => $eventfrontRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
         
                /** @var UploadedFile $imageFile */
                $imageFile = $form->get('image')->getData();
            
                // Gérer l'envoi du fichier
                if ($imageFile) {
                    // Définir le chemin de destination final
                    $destination = '/images';
                    // Générer un nom de fichier unique
                    $newFilename = uniqid().'.'.$imageFile->guessExtension();
                    // Déplacer le fichier vers le répertoire de destination avec un nom unique
                    $imageFile->move($destination, $newFilename);
                    // Enregistrer le chemin complet du fichier dans l'entité Event
                    $event->setImage($destination.'/'.$newFilename);
                }
                
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_eventt_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/showfr.html.twig', [
            'event' => $event,
        ]);
    }

   

   
}
