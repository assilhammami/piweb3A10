<?php

namespace App\Controller;

use App\Entity\Travail;
use App\Form\Travail1Type;
use App\Repository\TravailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;


#[Route('/client-travail')]
class ClientTravailController extends AbstractController
{
    #[Route('/', name: 'app_client_travail_index', methods: ['GET'])]
    public function index(Request $request,TravailRepository $travailRepository , PaginatorInterface $paginator): Response
    {
        $searchQuery = $request->query->get('q');

        if ($searchQuery) {
            $allTravaux = $travailRepository->findBySearchQuery($searchQuery);
        } else {
            $allTravaux = $travailRepository->findAll();
        }
 // Récupère tous les travaux depuis la base de données

    // Paginer les travaux avec KnpPaginatorBundle
    $travaux = $paginator->paginate(
        $allTravaux, // Les données à paginer
        $request->query->getInt('page', 1), // Numéro de la page, par défaut 1
        5 // Nombre d'éléments par page
    );
        
        return $this->render('client_travail/index.html.twig', [
            'travails' => $travaux,
        ]);
    }

    #[Route('/tt', name: 'app_client_travailbyid_index', methods: ['GET'])]
    public function index1(TravailRepository $travailRepository): Response
    {
        return $this->render('client_travail/indexbyid.html.twig', [
            'travails' => $travailRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_client_travail_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $travail = new Travail();
        $form = $this->createForm(Travail1Type::class, $travail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
 // Vérifier si le commentaire contient des mots interdits
 $comment = $travail->getTitre(); // Supposons que 'comment' est le champ de commentaire dans votre entité Avis
 if ($this->containsBadWords($comment)) {
     // Redirection ou gestion d'erreur
     return new Response('Votre Titre contient des mots interdits.', 400);
 }





 
            $entityManager->persist($travail);
            $entityManager->flush();

            return $this->redirectToRoute('app_client_travail_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('client_travail/new.html.twig', [
            'travail' => $travail,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_client_travail_show', methods: ['GET'])]
    public function show(Travail $travail): Response
    {
        return $this->render('client_travail/show.html.twig', [
            'travail' => $travail,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_client_travail_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Travail $travail, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Travail1Type::class, $travail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_client_travail_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('client_travail/edit.html.twig', [
            'travail' => $travail,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_client_travail_delete', methods: ['POST'])]
    public function delete(Request $request, Travail $travail, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$travail->getId(), $request->request->get('_token'))) {
            $entityManager->remove($travail);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_client_travail_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/prix', name:'travails_prix')]
     
    public function travailbyPrix(TravailRepository $DossierRepository): Response
    {
        $dossiers = $DossierRepository->findByPrix();
        
        return $this->render('client_travail/index.html.twig', [
            'dossiers' => $dossiers,
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
