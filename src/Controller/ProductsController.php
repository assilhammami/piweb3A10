<?php

namespace App\Controller;

use App\Entity\Products;
use App\Entity\WishList;
use App\Form\ProductsType;
use App\Entity\Cart;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
#[Route('/products')]
class ProductsController extends AbstractController
{
   /* #[Route('/', name: 'app_products_index', methods: ['GET'])]
    public function index(Request $request, ProductsRepository $productsRepository): Response
    {
        $query = $request->query->get('query');
    
        if ($query !== null) {
            $products = $productsRepository->findBySearchQuery($query);
        } else {
            // If no query is provided, return all products
            $products = $productsRepository->findAll();
        }
    
        return $this->render('products/index.html.twig', [
            'products' => $products,
            'productInWishlist' => [$this, 'productInWishlist'], // Pass the function to the Twig template
        ]);
    }*/
   /* #[Route('/', name: 'app_reclamation_index', methods: ['GET'])]
    public function index(ReclamationRepository $reclamationRepository,Request $request,PaginatorInterface $paginator): Response
    {

        $pagination = $paginator->paginate(
            $reclamationRepository->findAll(), // Query
            $request->query->getInt('page', 1), // Page number
            10 // Limit per page
        );

        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $pagination,
        ]);
    }*/
    #[Route('/', name: 'app_products_index', methods: ['GET'])]
    public function index(Request $request, ProductsRepository $productsRepository, PaginatorInterface $paginator): Response
    {
        $query = $request->query->get('query');
    
        if ($query !== null) {
            $queryBuilder = $productsRepository->getSearchQuery($query);
        } else {
            // If no query is provided, return all products
            $queryBuilder = $productsRepository->createQueryBuilder('p');
        }
    
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(), // Doctrine Query object
            $request->query->getInt('page', 1), // Current page number
            6 // Items per page
        );
    
        return $this->render('products/index.html.twig', [
            'products' => $pagination,
            'productInWishlist' => [$this, 'productInWishlist'], // Pass the function to the Twig template
        ]);
    }
    private function isInWishlist(int $productId, int $userId): bool
    {
        $wishlistItem = $this->getDoctrine()
            ->getRepository(WishList::class)
            ->findOneBy(['productId' => $productId, 'userId' => $userId]);

        return $wishlistItem !== null;
    }
    #[Route('/shop', name: 'app_shop_index', methods: ['GET'])]
    public function shop(Request $request, ProductsRepository $productsRepository): Response
    {
        $query = $request->query->get('query');
    
        if ($query !== null) {
            $products = $productsRepository->findBySearchQuery($query);
        } else {
            // If no query is provided, return all products
            $products = $productsRepository->findAll();
        }
    
        $userId = 1; // Assuming a fixed user for now
        $wishlistItems = $this->getDoctrine()->getRepository(WishList::class)->findBy(['userId' => $userId]);
    
        // Iterate through products to check if each product is in the wishlist
        foreach ($products as $product) {
            $product->isInWishlist = $this->isInWishlist($product->getId(), $userId);
        }
    
        // Render only the product list as a partial view
        if ($request->isXmlHttpRequest()) {
            return $this->render('products/front_office/product_list.html.twig', [
                'products' => $products,
            ]);
        }
    
        return $this->render('products/front_office/shop.html.twig', [
            'products' => $products,
        ]);
    }
    
    private function isProductUnique(Products $product): bool
{
    $repository = $this->getDoctrine()->getRepository(Products::class);
    $existingProduct = $repository->findOneBy([
        'Name' => $product->getName(),
        'Description' => $product->getDescription(),
    ]);

    return $existingProduct === null;
}

#[Route('/new', name: 'app_products_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $product = new Products();
    $todayDate = new \DateTime(); 
    $result = $todayDate->format('Y-m-d H:i:s');
    $product->setCreationDate($result);
    $form = $this->createForm(ProductsType::class, $product);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Check if the product already exists
        $isUnique = $this->isProductUnique($product);
        if (!$isUnique) {
            $this->addFlash('error', 'This product already exists.');
            return $this->redirectToRoute('app_products_new');
        }

        // Handle file upload
        $file = $form['Image']->getData();
        if ($file) {
            $uploadsDirectory = $this->getParameter('uploads_directory');
            $filename = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move($uploadsDirectory, $filename);
            $product->setImage($filename);
        }
        

        // Persist the product entity
        $entityManager->persist($product);
        $entityManager->flush();

        // Redirect to the index page
        return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('products/new.html.twig', [
        'product' => $product,
        'form' => $form->createView(),
    ]);
}


    #[Route('/{id}', name: 'app_products_show', methods: ['GET'])]
    public function show(Products $product): Response
    {
        return $this->render('products/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/shop/{id}', name: 'app_products_show_front', methods: ['GET'])]
    public function show_front(Products $product): Response
    {
        return $this->render('products/front_office/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_products_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Products $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Check if the product already exists
            $isUnique = $this->isProductUniqueExceptCurrent($product);
            if (!$isUnique) {
                $this->addFlash('error', 'This product already exists.');
                return $this->redirectToRoute('app_products_edit', ['id' => $product->getId()]);
            }
    
            // Handle file upload
            $file = $form['Image']->getData();
            if ($file) {
                $uploadsDirectory = $this->getParameter('uploads_directory');
                $filename = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move($uploadsDirectory, $filename);
                $product->setImage($filename);
            }
            $todayDate = new \DateTime(); 
            $result = $todayDate->format('Y-m-d H:i:s');
            $product->setCreationDate($result);
    
            $entityManager->flush();
    
            return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->renderForm('products/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }
    
    private function isProductUniqueExceptCurrent(Products $product): bool
    {
        $repository = $this->getDoctrine()->getRepository(Products::class);
        $existingProduct = $repository->findOneBy([
            'Name' => $product->getName(),
        ]);
        if ($existingProduct && $existingProduct->getId() !== $product->getId()) {
            return false;
        }

        return true;
    }


    #[Route('/{id}', name: 'app_products_delete', methods: ['POST'])]
    public function delete(Request $request, Products $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/add_to_cart/{id}', name: 'app_add_to_cart', methods: ['GET'])]
    public function addToCart(Products $product, EntityManagerInterface $entityManager): Response
    {
        $quantity = $product->getStock(); //verif quantity
        if ($quantity <= 0) {
            $this->addFlash('error', 'Product is out of stock.');
            return $this->redirectToRoute('app_shop_index');
        }
        $product->setStock($quantity - 1);
        $entityManager->flush();
        //ajout
        $cartItem = new Cart();
        $cartItem->setProductId($product->getId());
        $cartItem->setProductImage($product->getImage());
        $cartItem->setProductName($product->getName());
        $cartItem->setQuantity(1);
        $cartItem->setPrice($product->getPrice() * $cartItem->getQuantity());
        //$cartItem->setUserId($this->getUser()->getId());
        $cartItem->setUserId(1);
        $entityManager->persist($cartItem);
        $entityManager->flush();

        $this->addFlash('success', 'Product added to cart.');
        return $this->redirectToRoute('app_shop_index');
    }

    private function productInWishlist(int $productId): bool
    {
        $userId = 1;
        $wishlistItem = $this->getDoctrine()->getRepository(WishList::class)->findOneBy(['productId' => $productId, 'userId' => $userId]);
    
        return $wishlistItem !== null;
    }
    
}
