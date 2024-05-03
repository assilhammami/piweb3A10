<?php

namespace App\Controller;

use App\Entity\Travail;
use App\Form\Travail2Type;
use App\Repository\TravailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;



#[Route('/artiste/travail')]
class ArtisteTravailController extends AbstractController
{
    #[Route('/', name: 'app_artiste_travail_index', methods: ['GET'])]
    public function index(Request $request, TravailRepository $travailRepository, PaginatorInterface $paginator): Response
    {// Récupère tous les travaux depuis la base de données
        $allTravaux = $travailRepository->findAll();

        // Paginer les travaux avec KnpPaginatorBundle
        $travaux = $paginator->paginate(
            $allTravaux, // Les données à paginer
            $request->query->getInt('page', 1), // Numéro de la page, par défaut 1
            5 // Nombre d'éléments par page
        );
        return $this->render('artiste_travail/index.html.twig', [
            'travaux' => $travaux,
        ]);
    }

    #[Route('/new', name: 'app_artiste_travail_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $travail = new Travail();
        $form = $this->createForm(Travail2Type::class, $travail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($travail);
            $entityManager->flush();

            return $this->redirectToRoute('app_artiste_travail_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('artiste_travail/new.html.twig', [
            'travail' => $travail,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_artiste_travail_show', methods: ['GET'])]
    public function show(Travail $travail): Response
    {
        return $this->render('artiste_travail/show.html.twig', [
            'travail' => $travail,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_artiste_travail_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Travail $travail, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Travail2Type::class, $travail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_artiste_travail_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('artiste_travail/edit.html.twig', [
            'travail' => $travail,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_artiste_travail_delete', methods: ['POST'])]
    public function delete(Request $request, Travail $travail, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$travail->getId(), $request->request->get('_token'))) {
            $entityManager->remove($travail);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_artiste_travail_index', [], Response::HTTP_SEE_OTHER);
    }


    
}



