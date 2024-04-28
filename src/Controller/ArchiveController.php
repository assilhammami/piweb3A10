<?php

namespace App\Controller;

use App\Entity\Archive;
use App\Form\ArchiveType;
use App\Repository\ArchiveRepository;
use App\Repository\TravailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;


#[Route('/archive')]
class ArchiveController extends AbstractController
{
    #[Route('/', name: 'app_archive_index', methods: ['GET'])]
    public function index(ArchiveRepository $archiveRepository): Response
    {
        return $this->render('archive/index.html.twig', [
            'archives' => $archiveRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_archive_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $archive = new Archive();
        $form = $this->createForm(ArchiveType::class, $archive);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
           
            $entityManager->persist($archive);
            $entityManager->flush();

            return $this->redirectToRoute('app_archive_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('archive/new.html.twig', [
            'archive' => $archive,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_archive_show', methods: ['GET'])]
    public function show(Archive $archive): Response
    {
        return $this->render('archive/show.html.twig', [
            'archive' => $archive,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_archive_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Archive $archive, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArchiveType::class, $archive);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_archive_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('archive/edit.html.twig', [
            'archive' => $archive,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_archive_delete', methods: ['POST'])]
    public function delete(Request $request, Archive $archive, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$archive->getId(), $request->request->get('_token'))) {
            $entityManager->remove($archive);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_archive_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/', name: 'search', methods: ['POST'])]
    public function search(Request $request, ArchiveRepository $eventRepository): Response
    {
        
        $requestData = json_decode($request->getContent(), true);
        $searchValue = $requestData['search'] ?? ''; 
    
      
        if (empty($searchValue)) {
           
            $events = $eventRepository->findAll();
        } else {
           
            $events = $eventRepository->createQueryBuilder('e')
                ->where('e.description LIKE :searchValue')
                ->setParameter('searchValue', '%' . $searchValue . '%')
                ->getQuery()
                ->getResult();
        }
    
      
        return $this->render('Archive/table_rowss.html.twig', [
            'archives' => $events, 
        ]);
    
        }
   
    }







