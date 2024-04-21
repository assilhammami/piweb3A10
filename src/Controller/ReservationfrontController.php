<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Event;
use App\Form\Reservation3Type;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\PdfGeneratorService;



#[Route('/reservationfront')]
class ReservationfrontController extends AbstractController
{
    #[Route('/', name: 'app_reservationfront_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        return $this->render('reservationfront/index.html.twig', [
            'reservations' => $reservationRepository->findAll(),
        ]);
    }
    #[Route('/new/{eventId}', name: 'app_reservationfront_new', methods: ['GET', 'POST'])]
    public function new($eventId, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Trouver l'événement en fonction de son ID
        $event = $this->getDoctrine()->getRepository(Event::class)->find($eventId);
    
        // Vérifier si l'événement existe
        if (!$event) {
            throw $this->createNotFoundException('L\'événement avec l\'ID '.$eventId.' n\'existe pas.');
        }
    
        // Créer une nouvelle réservation et l'associer à l'événement
        $reservation = new Reservation();
        $reservation->setIdevent($event);
    
        // Créer le formulaire de réservation
        $form = $this->createForm(Reservation3Type::class, $reservation);
        $form->handleRequest($request);
    
        // Traiter la soumission du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Persister la réservation
            $entityManager->persist($reservation);
            $entityManager->flush();
    
            // Rediriger vers la liste des réservations ou toute autre page appropriée
            return $this->redirectToRoute('app_reservationfront_index');

        }
    
        // Afficher le formulaire de réservation
        return $this->renderForm('reservationfront/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }
    

    #[Route('/{id}', name: 'app_reservationfront_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservationfront/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservationfront_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Reservation3Type::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reservationfront_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reservationfront/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservationfront_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservationfront_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/pdf/reservation', name: 'generator_service_reservation')]
    public function pdfEvenement(): Response
    {
        $reservation= $this->getDoctrine()
            ->getRepository(Reservation::class)
            ->findAll();



        $html =$this->renderView('mpdf/index.html.twig', ['reservations' => $reservation]);
        $pdfGeneratorService=new PdfGeneratorService();
        $pdf = $pdfGeneratorService->generatePdf($html);

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"',
        ]);

}}
