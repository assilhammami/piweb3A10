<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Form\Cours2Type;
use App\Repository\CoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/cours-front')]
class CoursFrontController extends AbstractController
{
    
    #[Route('/', name: 'app_cours_front_index', methods: ['GET'])]
    public function index(CoursRepository $coursRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $coursRepository->findAll(); // Récupérer tous les cours
    
        $cours = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), // Numéro de la page. Par défaut, 1
            3 // Nombre d'éléments par page
        );
    
        return $this->render('cours_front/index.html.twig', [
            'cours' => $cours,
        ]);
    }

    #[Route('/new', name: 'app_cours_front_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cour = new Cours();
        $form = $this->createForm(Cours2Type::class, $cour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cour);
            $entityManager->flush();

            return $this->redirectToRoute('app_cours_front_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cours_front/new.html.twig', [
            'cour' => $cour,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cours_front_show', methods: ['GET'])]
    public function show(Cours $cour): Response
    {
        return $this->render('cours_front/show.html.twig', [
            'cour' => $cour,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_cours_front_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cours $cour, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Cours2Type::class, $cour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_cours_front_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cours_front/edit.html.twig', [
            'cour' => $cour,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cours_front_delete', methods: ['POST'])]
    public function delete(Request $request, Cours $cour, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cour->getId(), $request->request->get('_token'))) {
            $entityManager->remove($cour);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cours_front_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/', name: 'search', methods: ['POST'])]
    public function search(Request $request, CoursRepository $coursRepository,PaginatorInterface $paginator): Response
    {
        
        $requestData = json_decode($request->getContent(), true);
        $searchValue = $requestData['search'] ?? ''; 
    
      
        if (empty($searchValue)) {
           
        $cour = $coursRepository->findAll(); // Récupérer tous les cours
    
        } else {
           
            $cour = $coursRepository->createQueryBuilder('e')
                ->where('e.nom LIKE :searchValue')
                ->setParameter('searchValue', '%' . $searchValue . '%')
                ->getQuery()
                ->getResult();
        }
    
      
        return $this->render('cours_front/search.html.twig', [
            'cour' => $cour, 
        ]);
    
        }
    
}
