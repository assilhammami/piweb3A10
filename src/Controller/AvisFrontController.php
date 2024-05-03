<?php

namespace App\Controller;
use App\Entity\Cours;
use App\Entity\Avis;
use App\Form\Avis1Type;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/avis-front')]
class AvisFrontController extends AbstractController
{
    #[Route('/', name: 'app_avis_front_index', methods: ['GET'])]
    public function index(AvisRepository $avisRepository): Response
    {
        return $this->render('avis_front/index.html.twig', [
            'avis' => $avisRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_avis_front_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $avi = new Avis();
        $form = $this->createForm(Avis1Type::class, $avi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si le commentaire contient des mots interdits
            $comment = $avi->getCommentaire(); // Supposons que 'comment' est le champ de commentaire dans votre entité Avis
            if ($this->containsBadWords($comment)) {
                // Redirection ou gestion d'erreur
                return new Response('Votre commentaire contient des mots interdits.', 400);
            }

            $entityManager->persist($avi);
            $entityManager->flush();

            return $this->redirectToRoute('app_avis_front_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('avis_front/new.html.twig', [
            'avi' => $avi,
            'form' => $form,
        ]);
    }



    #[Route('/{id}', name: 'app_avis_front_show', methods: ['GET'])]
    public function show(Avis $avi): Response
    {
        return $this->render('avis_front/show.html.twig', [
            'avi' => $avi,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_avis_front_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Avis $avi, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Avis1Type::class, $avi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_avis_front_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('avis_front/edit.html.twig', [
            'avi' => $avi,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_avis_front_delete', methods: ['POST'])]
    public function delete(Request $request, Avis $avi, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$avi->getId(), $request->request->get('_token'))) {
            $entityManager->remove($avi);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_avis_front_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/new/{coursId}', name: 'app_aviss_front_new', methods: ['GET', 'POST'])]
    public function new1($coursId, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Trouver l'cours en fonction de son ID
        $cours = $this->getDoctrine()->getRepository(Cours::class)->find($coursId);
    
        // Vérifier si l'cours existe
        if (!$cours) {
            throw $this->createNotFoundException('L\'événement avec l\'ID '.$coursId.' n\'existe pas.');
        }
    
        // Créer une nouvelle avis et l'associer à l'cours
        $avis = new Avis();
        $avis->setIdCour($cours);
    
        // Créer le formulaire de l'avis
        $form = $this->createForm(Avis1Type::class, $avis);
        $form->handleRequest($request);
    
        // Traiter la soumission du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Persister l' avis
            $entityManager->persist($avis);
            $entityManager->flush();
    
            // Rediriger vers la liste des avis ou toute autre page appropriée
            return $this->redirectToRoute('app_avis_front_index');

        }
    
        // Afficher le formulaire de réservation
        return $this->renderForm('avis_front/new.html.twig', [
            'avis' => $avis,
            'form' => $form,
        ]);
    }

    public function containsBadWords($comment)
    {
        // Récupérer la liste des mots interdits depuis les paramètres Symfony
        $badWords = $this->getParameter('badwords');

        // Vérifier si le commentaire contient l'un des mots interdits
        foreach ($badWords as $word) {
            if (stripos($comment, $word) !== false) {
                return true; // Le commentaire contient un mot interdit
            }
        }

        return false; // Le commentaire ne contient aucun mot interdit
    }
}

