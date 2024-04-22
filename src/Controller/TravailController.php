<?php

namespace App\Controller;

use App\Entity\Travail;
use App\Form\TravailType;
use App\Repository\TravailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/travail')]
class TravailController extends AbstractController
{
    #[Route('/', name: 'app_travail_index', methods: ['GET'])]
    public function index(Request $request, TravailRepository $travailRepository, PaginatorInterface $paginator): Response
    {
        // Récupère tous les travaux depuis la base de données
        $allTravaux = $travailRepository->findAll();

        // Paginer les travaux avec KnpPaginatorBundle
        $travaux = $paginator->paginate(
            $allTravaux, // Les données à paginer
            $request->query->getInt('page', 1), // Numéro de la page, par défaut 1
            5 // Nombre d'éléments par page
        );

        return $this->render('travail/index.html.twig', [
            'travaux' => $travaux,
        ]);
    }

    #[Route('/new', name: 'app_travail_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $travail = new Travail();
        $travail->setDateDemande(new \DateTime('today'));
        $form = $this->createForm(TravailType::class, $travail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($travail->getDateFin() < $travail->getDateDemande()) {
                $form->get('date_fin')->addError(new FormError('La date de fin doit être supérieure ou égale à la date de demande.'));
                return $this->renderForm('travail/new.html.twig', [
                    'travail' => $travail,
                    'form' => $form,
                ]);
            }
            $entityManager->persist($travail);
            $entityManager->flush();

            return $this->redirectToRoute('app_travail_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('travail/new.html.twig', [
            'travail' => $travail,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_travail_show', methods: ['GET'])]
    public function show(Travail $travail): Response
    {
        return $this->render('travail/show.html.twig', [
            'travail' => $travail,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_travail_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Travail $travail, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TravailType::class, $travail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_travail_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('travail/edit.html.twig', [
            'travail' => $travail,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_travail_delete', methods: ['POST'])]
    public function delete(Request $request, Travail $travail, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$travail->getId(), $request->request->get('_token'))) {
            $entityManager->remove($travail);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_travail_index', [], Response::HTTP_SEE_OTHER);
    }

    
    
}
