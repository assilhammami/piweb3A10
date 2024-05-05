<?php

namespace App\Controller;

use App\Entity\Likes;
use App\Repository\likesRepository;


use App\Form\Likes3Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/likes')]
class LikesController extends AbstractController
{
    #[Route('/', name: 'app_likes_index', methods: ['GET'])]
    public function index(LikesRepository $likesRepository): Response
    {
        return $this->render('likes/index.html.twig', [
            'likes' => $likesRepository->findAll(),
        ]);
    }

    
    

    #[Route('/new', name: 'app_likes_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $like = new Likes();
        $form = $this->createForm(Likes3Type::class, $like);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($like);
            $entityManager->flush();

            return $this->redirectToRoute('app_likes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('likes/new.html.twig', [
            'like' => $like,
            'form' => $form,
        ]);
    }

    #[Route('/{likeId}', name: 'app_likes_show', methods: ['GET'])]
    public function show(Likes $like): Response
    {
        return $this->render('likes/show.html.twig', [
            'like' => $like,
        ]);
    }

    #[Route('/{likeId}/edit', name: 'app_likes_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Likes $like, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Likes3Type::class, $like);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_likes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('likes/edit.html.twig', [
            'like' => $like,
            'form' => $form,
        ]);
    }

    #[Route('/{likeId}', name: 'app_likes_delete', methods: ['POST'])]
    public function delete(Request $request, Likes $like, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$like->getLikeId(), $request->request->get('_token'))) {
            $entityManager->remove($like);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_likes_index', [], Response::HTTP_SEE_OTHER);
    }
}