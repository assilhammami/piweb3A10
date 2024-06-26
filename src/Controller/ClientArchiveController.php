<?php

namespace App\Controller;
use App\Entity\PdfGeneratorService;
use App\Entity\Archive;
use App\Form\Archive1Type;
use App\Repository\ArchiveRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/client/archive')]
class ClientArchiveController extends AbstractController
{
    #[Route('/', name: 'app_client_archive_index', methods: ['GET'])]
    public function index(ArchiveRepository $archiveRepository): Response
    {
        return $this->render('client_archive/index.html.twig', [
            'archives' => $archiveRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_client_archive_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $archive = new Archive();
        $form = $this->createForm(Archive1Type::class, $archive);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($archive);
            $entityManager->flush();

            return $this->redirectToRoute('app_client_archive_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('client_archive/new.html.twig', [
            'archive' => $archive,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_client_archive_show', methods: ['GET'])]
    public function show(Archive $archive): Response
    {
        return $this->render('client_archive/show.html.twig', [
            'archive' => $archive,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_client_archive_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Archive $archive, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Archive1Type::class, $archive);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_client_archive_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('client_archive/edit.html.twig', [
            'archive' => $archive,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_client_archive_delete', methods: ['POST'])]
    public function delete(Request $request, Archive $archive, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$archive->getId(), $request->request->get('_token'))) {
            $entityManager->remove($archive);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_client_archive_index', [], Response::HTTP_SEE_OTHER);
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

}



}
