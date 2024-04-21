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
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
#[Route('/backoffice/event')]
class EventController extends AbstractController
{
    #[Route('/', name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('event/index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }

    
    #[Route('/new', name: 'app_eventt_new', methods: ['GET', 'POST'])]
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
                // Récupérer le nom d'origine du fichier
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // Définir le chemin de destination final
                $destination = $this->getParameter('kernel.project_dir').'/public/images';
    
                // Générer un nom de fichier unique basé sur le nom d'origine et l'extension
                $newFilename = $originalFilename . '_' . uniqid() . '.' . $imageFile->guessExtension();
                // Déplacer le fichier vers le répertoire de destination avec le nouveau nom
                $imageFile->move($destination, $newFilename);
                // Enregistrer le nom du fichier dans l'entité Event
                $event->setImage($newFilename);
            }
            
            // Persistez l'entité dans la base de données
            $entityManager->persist($event);
            $entityManager->flush();
        
            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }
        
        return $this->renderForm('event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }
    

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit($id,Request $request, Event $event, EntityManagerInterface $entityManager,EventRepository $repo): Response
    {
        $event=$repo->find($id);
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();
            
            // Gérer l'envoi du fichier
            if ($imageFile) {
                // Récupérer le nom d'origine du fichier
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // Définir le chemin de destination final
                $destination = $this->getParameter('kernel.project_dir').'/public/images';
    
                // Générer un nom de fichier unique basé sur le nom d'origine et l'extension
                $newFilename = $originalFilename . '_' . uniqid() . '.' . $imageFile->guessExtension();
                // Déplacer le fichier vers le répertoire de destination avec le nouveau nom
                $imageFile->move($destination, $newFilename);
                // Enregistrer le nom du fichier dans l'entité Event
                $event->setImage($newFilename);
            }

        return $this->renderForm('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }
    }
    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }
}
