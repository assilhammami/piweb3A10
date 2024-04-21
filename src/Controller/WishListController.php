<?php

namespace App\Controller;

use App\Entity\WishList;
use App\Entity\Products;
use App\Form\WishListType;
use App\Repository\WishListRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/wishlist')]
class WishListController extends AbstractController
{

    #[Route('/add/{id}', name: 'app_add_to_wishlist', methods: ['GET','POST'])]
    public function addToWishList(Products $product, EntityManagerInterface $entityManager): Response
    {
        // Create a new wishlist item
        $wishListItem = new WishList();
        $wishListItem->setProductId($product->getId());
        $wishListItem->setProductImage($product->getImage());
        $wishListItem->setProductName($product->getName());
        // Assuming you have a user system and the user is authenticated
        $wishListItem->setUserId(1);

        // Persist the wishlist item to the database
        $entityManager->persist($wishListItem);
        $entityManager->flush();

        // Optionally, add a flash message to indicate success
        $this->addFlash('success', 'Product added to wishlist.');

        // Redirect the user to the shop page or wherever desired
        return $this->redirectToRoute('app_shop_index');
        
    }


    #[Route('/', name: 'app_wish_list_index', methods: ['GET'])]
    public function index(WishListRepository $wishListRepository): Response
    {
        return $this->render('wish_list/front_office/index.html.twig', [
            'wish_lists' => $wishListRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_wish_list_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $wishList = new WishList();
        $form = $this->createForm(WishListType::class, $wishList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($wishList);
            $entityManager->flush();

            return $this->redirectToRoute('app_wish_list_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('wish_list/new.html.twig', [
            'wish_list' => $wishList,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_wish_list_show', methods: ['GET'])]
    public function show(WishList $wishList): Response
    {
        return $this->render('wish_list/show.html.twig', [
            'wish_list' => $wishList,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_wish_list_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, WishList $wishList, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WishListType::class, $wishList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_wish_list_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('wish_list/edit.html.twig', [
            'wish_list' => $wishList,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_wish_list_delete', methods: ['GET','POST'])]
    public function delete(Request $request, WishList $wishList, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$wishList->getId(), $request->request->get('_token'))) {
            $entityManager->remove($wishList);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_wish_list_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/remove/{id}', name: 'app_remove_from_wishlist', methods: ['GET','POST'])]
    public function removeFromWishList(Products $product, EntityManagerInterface $entityManager): Response
    {
       // $userId = $this->getUser()->getId();
        $userId = 1;
        $wishlistItem = $entityManager->getRepository(WishList::class)->findOneBy(['productId' => $product->getId(), 'userId' => $userId]);

        if ($wishlistItem) {
            $entityManager->remove($wishlistItem);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_shop_index');
    }
}
