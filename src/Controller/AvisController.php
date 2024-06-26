<?php

namespace App\Controller;
use App\Entity\PdfGeneratorService;
use App\Entity\Avis;
use App\Entity\Cours;
use App\Form\AvisType;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
#[Route('/avis')]
class AvisController extends AbstractController
{
    #[Route('/', name: 'app_avis_index', methods: ['GET'])]
    public function index(AvisRepository $avisRepository, PaginatorInterface $paginator, Request $request): Response
    {  $query = $avisRepository->findAll(); // Récupérer tous les avis
    
        $avis = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), // Numéro de la page. Par défaut, 1
            3// Nombre d'éléments par page
        );
        return $this->render('avis/index.html.twig', [
            'avis' => $avis,
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
        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);
    
        // Traiter la soumission du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Persister l' avis
            $entityManager->persist($avis);
            $entityManager->flush();
    
            // Rediriger vers la liste des avis ou toute autre page appropriée
            return $this->redirectToRoute('app_avis_index');

        }
    
        // Afficher le formulaire de réservation
        return $this->renderForm('avis/new.html.twig', [
            'avis' => $avis,
            'form' => $form,
        ]);
    }

    
    #[Route('/pdf/avis', name: 'generator_service_avis')]
    public function pdfAvis(): Response
    {
        $avis= $this->getDoctrine()
            ->getRepository(Avis::class)
            ->findAll();



        $html =$this->renderView('mpdf/index.html.twig', ['avis' => $avis]);
        $pdfGeneratorService=new PdfGeneratorService();
        $pdf = $pdfGeneratorService->generatePdf($html);

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"',
        ]);

}
}
