<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Cours;
use App\Form\AvisType;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/avis')]
class AvisController extends AbstractController
{
    #[Route('/', name: 'app_avis_index', methods: ['GET'])]
    public function index(AvisRepository $avisRepository): Response
    {
        return $this->render('avis/index.html.twig', [
            'avis' => $avisRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_avis_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $avi = new Avis();
        $form = $this->createForm(AvisType::class, $avi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($avi);
            $entityManager->flush();

            return $this->redirectToRoute('app_avis_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('avis/new.html.twig', [
            'avi' => $avi,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_avis_show', methods: ['GET'])]
    public function show(Avis $avi): Response
    {
        return $this->render('avis/show.html.twig', [
            'avi' => $avi,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_avis_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Avis $avi, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AvisType::class, $avi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_avis_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('avis/edit.html.twig', [
            'avi' => $avi,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_avis_delete', methods: ['POST'])]
    public function delete(Request $request, Avis $avi, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$avi->getId(), $request->request->get('_token'))) {
            $entityManager->remove($avi);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_avis_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/new/{coursId}', name: 'app_aviss_new', methods: ['GET', 'POST'])]
    public function new1($coursId, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Trouver l'événement en fonction de son ID
        $cours = $this->getDoctrine()->getRepository(Cours::class)->find($coursId);
    
        // Vérifier si l'événement existe
        if (!$cours) {
            throw $this->createNotFoundException('L\'événement avec l\'ID '.$coursId.' n\'existe pas.');
        }
    
        // Créer une nouvelle réservation et l'associer à l'événement
        $avis = new Avis();
        $avis->setIdCour($cours);
    
        // Créer le formulaire de réservation
        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);
    
        // Traiter la soumission du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Persister la réservation
            $entityManager->persist($avis);
            $entityManager->flush();
    
            // Rediriger vers la liste des réservations ou toute autre page appropriée
            return $this->redirectToRoute('app_avis_index');

        }
    
        // Afficher le formulaire de réservation
        return $this->renderForm('avis/new.html.twig', [
            'avis' => $avis,
            'form' => $form,
        ]);
    }
}
