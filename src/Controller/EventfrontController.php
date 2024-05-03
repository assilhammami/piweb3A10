<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Likes;
use App\Form\EventType;
use App\Form\Likes3Type;
use App\Repository\EventRepository;
use App\Repository\likesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\EventSearchType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;



#[Route('/frontoffice/event')]
class EventfrontController extends AbstractController
{
  

    #[Route('/', name: 'app_frontevent_index', methods: ['GET','POST'])]
    public function index(Request $request, EventRepository  $eventfrontRepository,LikesRepository $likesRepository, PaginatorInterface $paginator,EntityManagerInterface $entityManager): Response
    {
        // Récupère tous les travaux depuis la base de données
        $allTravaux = $eventfrontRepository->findAll();
        $likes = new likes();
        $form1 = $this->createForm(Likes3Type::class, $likes);

        $form1->handleRequest($request);
        if ($form1->isSubmitted() ) {
           
            $existingLike = $likesRepository->findOneBy([
                'postId' => $likes->getPostId(),
                'userId' => $likes->getUserId()
            ]);
        
            if ($existingLike) {
                // If the existing like has the same reaction type, delete it
                if ($existingLike->getReactionType() === $likes->getReactionType()) {
                    $entityManager->remove($existingLike);
                } else {
                    // If the reaction type is different, update the existing like
                    $existingLike->setReactionType($likes->getReactionType());
                    $entityManager->persist($existingLike);
                }
            } else {
                // If no existing like, persist the new like
                $entityManager->persist($likes);
            }
        

            $entityManager->flush();    
            return $this->redirectToRoute('app_frontevent_index', [], Response::HTTP_SEE_OTHER);
            
        }

        // Paginer les travaux avec KnpPaginatorBundle
        $event = $paginator->paginate(
            $allTravaux, // Les données à paginer
            $request->query->getInt('page', 1), // Numéro de la page, par défaut 1
            4 // Nombre d'éléments par page
        );

        return $this->renderForm('event/front.html.twig', [
            'events' => $event,
            'form1' => $form1,
            'likes' => $likesRepository->findAll(),
            'userid' => 5,
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

  ////////////////////////////////////////RECHERCHE/////////////////////////////
  
    }

   

