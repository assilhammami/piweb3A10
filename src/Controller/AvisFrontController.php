<?php

namespace App\Controller;

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
}

