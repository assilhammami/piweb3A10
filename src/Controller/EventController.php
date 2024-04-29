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
use App\Form\EventSearchType;

use Symfony\Component\HttpFoundation\JsonResponse;



#[Route('/backoffice/event')]
class EventController extends AbstractController
{
    #[Route('/', name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        $evenements = $eventRepository->findAll();
        $events = [];
    
        foreach ($evenements as $evenement) {
            $events[] = [
                'title' => $evenement->getNom(),
                'start' => $evenement->getDate(),
                'description' => $evenement->getDescription(),
                'url' => $this->generateUrl('app_event_show', ['id' => $evenement->getId()]),
                // Ajoutez d'autres attributs selon vos besoins
            ];
        }
    
        // Render la vue calendar.html.twig avec les événements
        return $this->render('event/index.html.twig', [
            'events' => $events,
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
            
            
            // Persistez l'entité dans la base de données
            $entityManager->persist($event);
            $entityManager->flush();
        
            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }
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
    public function edit($id, Request $request, Event $event, EntityManagerInterface $entityManager, EventRepository $repo): Response {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();
    
            // Si une nouvelle image a été téléchargée, traitez l'image
            if ($imageFile) {
                // Définir le chemin de destination final
                $destination = $this->getParameter('kernel.project_dir') . '/public/images';
    
                // Générer un nom de fichier unique
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
    
                // Déplacer le fichier vers le répertoire de destination
                $imageFile->move($destination, $newFilename);
    
                // Supprimer l'ancienne image si elle existe
                if ($event->getImage()) {
                    $oldImagePath = $destination . '/' . $event->getImage();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
    
                // Mettre à jour l'entité Event avec le nouveau nom de fichier
                $event->setImage($newFilename);
            }
    
            // Sauvegarder les modifications dans la base de données
            $entityManager->flush();
    
            // Rediriger vers la page de détails de l'événement
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }
    
        return $this->renderForm('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
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
    #[Route('/', name: 'search', methods: ['POST'])]
    public function search(Request $request, EventRepository $eventRepository): Response
    {
        
        $requestData = json_decode($request->getContent(), true);
        $searchValue = $requestData['search'] ?? ''; 
    
      
        if (empty($searchValue)) {
           
            $events = $eventRepository->findAll();
        } else {
           
            $events = $eventRepository->createQueryBuilder('e')
                ->where('e.nom LIKE :searchValue')
                ->setParameter('searchValue', '%' . $searchValue . '%')
                ->getQuery()
                ->getResult();
        }
    
      
        return $this->render('event/table_rows.html.twig', [
            'events' => $events, 
        ]);
    
        }
      #[Route('/calendar', name: 'app_evenement_calendar', methods: ['GET'])]
public function calendar(EventRepository $eventRepository): Response
{
    // Récupérer tous les événements de la base de données
    $evenements = $eventRepository->findAll();
    $events = [];

    // Transformer les événements en tableau
    foreach ($evenements as $evenement) {
        $events[] = [
            'title' => $evenement->getNom(),
            'start' => $evenement->getDate(),
            'description' => $evenement->getDescription(),
            'url' => $this->generateUrl('app_eventt_show', ['id' => $evenement->getId()]),
            // Vous pouvez ajouter plus d'attributs ici si nécessaire
        ];
    }

    // Rendre la vue Twig avec les événements passés comme paramètre
    return $this->render('event/calendar.html.twig', [
        'events' => $events,
    ]);
}

      #[Route("/stats", name:"stats")]
      #[ParamConverter("event", class:"App\Entity\Event")]
    public function statistiques(EventRepository $publicationRepository): Response
    {
        $publications = $publicationRepository->findAll();
        $data = [];

        // Récupérer le nombre de commentaires pour chaque publication
        foreach ($publications as $publication) {
            $data[] = [
                'nom' => $publication->getNom(),
                'capacity' => $publication->getCapacity()
            ];
        }

        return $this->render('event/chart.html.twig', [
            'data' => json_encode($data)
        ]);
    }

}