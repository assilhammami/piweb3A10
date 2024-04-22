<?php

namespace App\Controller;

use App\Entity\Archive;
use App\Entity\PdfGeneratorService;
use App\Entity\Travail;
use App\Form\Archive2Type;
use App\Repository\ArchiveRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/artiste/archive')]
class ArtisteArchiveController extends AbstractController
{
    #[Route('/', name: 'app_artiste_archive_index', methods: ['GET'])]
    public function index(ArchiveRepository $archiveRepository): Response
    {
        return $this->render('artiste_archive/index.html.twig', [
            'archives' => $archiveRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_artiste_archive_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $archive = new Archive();
        $form = $this->createForm(Archive2Type::class, $archive);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($archive);
            $entityManager->flush();

            return $this->redirectToRoute('app_artiste_archive_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('artiste_archive/new.html.twig', [
            'archive' => $archive,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_artiste_archive_show', methods: ['GET'])]
    public function show(Archive $archive): Response
    {
        return $this->render('artiste_archive/show.html.twig', [
            'archive' => $archive,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_artiste_archive_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Archive $archive, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Archive2Type::class, $archive);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_artiste_archive_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('artiste_archive/edit.html.twig', [
            'archive' => $archive,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_artiste_archive_delete', methods: ['POST'])]
    public function delete(Request $request, Archive $archive, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$archive->getId(), $request->request->get('_token'))) {
            $entityManager->remove($archive);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_artiste_archive_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/new/{travailId}', name: 'app_reservationfront_new', methods: ['GET', 'POST'])]
    public function new1($travailId, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Trouver l'événement en fonction de son ID
        $tarvail = $this->getDoctrine()->getRepository(Travail::class)->find($travailId);
    
        // Vérifier si l'événement existe
        if (!$tarvail) {
            throw $this->createNotFoundException('L\'événement avec l\'ID '.$travailId.' n\'existe pas.');
        }
    
        // Créer une nouvelle réservation et l'associer à l'événement
        $archive = new Archive();
        $archive->setIdT($tarvail);
    
        // Créer le formulaire de réservation
        $form = $this->createForm(Archive2Type::class, $archive);
        $form->handleRequest($request);
    
        // Traiter la soumission du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Persister la réservation
            $entityManager->persist($archive);
            $entityManager->flush();
    
            // Rediriger vers la liste des réservations ou toute autre page appropriée
            return $this->redirectToRoute('app_artiste_archive_index');

        }
    
        // Afficher le formulaire de réservation
        return $this->renderForm('artiste_archive/new.html.twig', [
            'archive' => $archive,
            'form' => $form,
        ]);
    }
    #[Route('/pdf/reservation', name: 'generator_service_reservation')]
    public function pdfEvenement(): Response
    {
        $archive= $this->getDoctrine()
            ->getRepository(Archive::class)
            ->findAll();



        $html =$this->renderView('mpdf/index.html.twig', ['archives' => $archive]);
        $pdfGeneratorService=new PdfGeneratorService();
        $pdf = $pdfGeneratorService->generatePdf($html);

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"',
        ]);

}}


