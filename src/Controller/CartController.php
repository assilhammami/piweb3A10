<?php

namespace App\Controller;

use App\Entity\CustomerOrder;
use App\Entity\Orders;
use App\Entity\Products;
use App\Entity\Cart;
use App\Form\CartType;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CustomerOrderRepository;
use App\Service\PdfGenerator;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Twilio\Rest\Client;
use App\Entity\WhatsappNotif;




class CartController extends AbstractController
{
    private $pdfGenerator;

    public function __construct(PdfGenerator $pdfGenerator)
    {
        $this->pdfGenerator = $pdfGenerator;
    }

    #[Route('/cart/', name: 'app_cart_index', methods: ['GET'])]
    public function index(CartRepository $cartRepository): Response
    {
        return $this->render('cart/index.html.twig', [
            'carts' => $cartRepository->findAll(),
        ]);
    }

    #[Route('/cart/new', name: 'app_cart_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cart = new Cart();
        $form = $this->createForm(CartType::class, $cart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cart);
            $entityManager->flush();

            return $this->redirectToRoute('app_cart_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cart/new.html.twig', [
            'cart' => $cart,
            'form' => $form,
        ]);
    }

    #[Route('/cart/{id}', name: 'app_cart_show', methods: ['GET'])]
    public function show(Cart $cart): Response
    {
        return $this->render('cart/show.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/cart/{id}/edit', name: 'app_cart_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cart $cart, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CartType::class, $cart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_cart_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cart/edit.html.twig', [
            'cart' => $cart,
            'form' => $form,
        ]);
    }

    #[Route('/cart/{id}', name: 'app_cart_delete', methods: ['POST'])]
    public function delete(Request $request, Cart $cart, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cart->getId(), $request->request->get('_token'))) {
            $entityManager->remove($cart);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cart_index', [], Response::HTTP_SEE_OTHER);
    }

//  public function index(SessionInterface $session, productsRepository $productsRepository)
// {
// $cart = $session->get('cart', []);

// $cartAvecData = [];
// foreach($cart as $id => $quantite)
// {
// $cartAvecData[] = [
// 'products' => $productsRepository->find($id),
// 'quantite' => $quantite
// ];
// }

// $total = 0;

// foreach($cartAvecData as $prod)
// {
// $totalProd = $prod['products']->getPrixProduit() * $prod['quantite'];
// $total += $totalProd;
// }
// return $this->render('cart/index.html.twig', [
// 'prods' => $cartAvecData,
// 'total' => $total
// ]);
// }
    

#[Route('/cart/add_to_order', name: 'app_add_to_order', methods: ['GET'])]
public function addToOrder(EntityManagerInterface $entityManager): Response
{
    // Add product to order
    $orderItem = new CustomerOrder();
    $orderItem->setUserId(1);
    $entityManager->persist($orderItem);
    $entityManager->flush();

    $this->addFlash('success', 'Order passed.');
    return $this->redirectToRoute('app_shop_index');
}

#[Route('/cart_checkout', name: 'app_cart_checkout', methods: ['GET'])]
public function checkout(CartRepository $cartRepository, EntityManagerInterface $entityManager): Response
{
    // Retrieve all items from the cart for userId = 1
    $cartItems = $cartRepository->findBy(['userId' => 1]);
    
    // Iterate over each cart item
    foreach ($cartItems as $cartItem) {
        // Create a new order entity
        $order = new Orders();
        $orderDate = date('Y-m-d H:i:s');
        $order->setOrderDate($orderDate); // Assuming you want the current date
        $order->setTotalPrice($cartItem->getPrice() * $cartItem->getQuantity());
        $order->setQuantity($cartItem->getQuantity());
        $order->setProductName($cartItem->getProductName());
        $order->setUserName('User 1'); // Assuming a default user name for now
        
        
        // Assuming you have a Products entity linked to the Cart entity
        // You may need to adjust this part based on your actual entity relations
        $product = $entityManager->getRepository(Products::class)->find($cartItem->getProductId());
        $order->setProducts($product);

        // Persist the order entity
        $entityManager->persist($order);

        // Remove the item from the cart
        $entityManager->remove($cartItem);
    }

    // Flush changes to the database
    $entityManager->flush();

    // Redirect to some page indicating successful checkout
    return $this->redirectToRoute('app_shop_index');
}

#[Route('/cart_buy_all', name: 'cart_buy_all', methods: ['GET','POST'])]
public function buyAll(CartRepository $cartRepository,Request $request, EntityManagerInterface $entityManager, CustomerOrderRepository $orderRepository): Response
{
    $sid = "AC02b753da6a37d9b0d7a310f7aa15d9cf";
    $token = "5131a077a1524160fb8299395019354b";
    $twilio = new Client($sid, $token);
    $userId = 1; // Set userId to 1
    
    $cartItems = $this->getDoctrine()->getRepository(Cart::class)->findBy(['userId' => $userId]);
       // Check if cart is empty
       if (empty($cartItems)) {
        // Redirect back or return some response indicating that there are no products in the cart
        return $this->redirectToRoute('app_cart_index');
    }
    $order = new CustomerOrder();
    $order->setUserId($userId);
    $order->setOrderDate(new \DateTime());
    $order->setStatus("In progress...");
    
    foreach ($cartItems as $item) {
        $order->addProduct([
            'productName' => $item->getProductName(),
            'productImage' => $item->getProductImage(),
            'quantity' => $item->getQuantity(),
            'price' => $item->getPrice() * $item->getQuantity(),

        ]);
        
        $entityManager->remove($item);
    }
    
    try {
        // Sending WhatsApp message
       
      
             // Create a PDF content
     $pdfContent = $this->generatePdfContent($cartItems);

     // Set response headers for PDF download
     $response = new Response($pdfContent);
     $response->headers->set('Content-Type', 'application/pdf');
     $response->headers->set('Content-Disposition', 'attachment; filename="order_receipt.pdf"');
        $entityManager->persist($order);
        $entityManager->flush();

        $message = $twilio->messages->create(
            "whatsapp:+21652601504",
            [
                "from" => "whatsapp:+14155238886",
                "body" => "A new order has been submitted."
            ]
        );

        // Saving WhatsApp notification
        $whatsappNotif = new WhatsappNotif();
        $whatsappNotif->setText("A new order has been added.");
        $whatsappNotif->setReclamation($order);
        $entityManager->persist($whatsappNotif);

        return $this->render('cart/index.html.twig', [
            'carts' => $cartRepository->findAll(),
        ]);
    

}
    catch (\Exception $e) {
        // Handle exception
        $this->addFlash('error', "Failed to send WhatsApp message: " . $e->getMessage());
    }
    return $this->redirectToRoute('generate_pdf');
}
// Generate PDF content based on cart items

private function generatePdfContent(array $cartItems): string
{
    // Generate the HTML content for the receipt
    $htmlContent = $this->renderView('pdf/receipt.html.twig', [
        'cartItems' => $cartItems,
    ]);

    // Use the PdfGenerator service to generate the PDF
    return $this->pdfGenerator->generatePdf($htmlContent,'test.pdf');
}
#[Route('/generate-pdf', name: 'generate_pdf')]
public function generatePdf(PdfGenerator $pdfGenerator): Response
{
    // Assuming you have HTML content for the PDF stored in a variable named $htmlContent
    $htmlContent = '<html><body><h1>Hello, World!</h1></body></html>';

    // Generate the PDF using the PdfGenerator service
    $pdfContent = $pdfGenerator->generatePdf($htmlContent, 'example.pdf');

    // Create a Symfony response with PDF content
    $response = new Response($pdfContent);

    // Set the content type to PDF
    $response->headers->set('Content-Type', 'application/pdf');

    // Set the content disposition to attachment to force download
    $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        'example.pdf'
    ));

    // Return the response
    return $response;
}
// Helper function to create a response with redirect
private function createResponseWithRedirect(Response $response, Response $redirectResponse): Response
{
    $response->headers->add($redirectResponse->headers->allPreserveCase());
    $response->setStatusCode($redirectResponse->getStatusCode());

    return $response;
}

#[Route('/cart/{id}/update_quantity', name: 'app_cart_update_quantity', methods: ['POST'])]
public function updateQuantity(Request $request, Cart $cart, EntityManagerInterface $entityManager): Response
{
    $newQuantity = $request->request->get('quantity');
    if ($newQuantity < 1) {
        return $this->redirectToRoute('app_cart_index');
    }

    // $product = $cart->getProduct();
    // if ($newQuantity > $product->getStock()) {
    //     $newQuantity = $product->getStock();
    // }
    $cart->setQuantity($newQuantity);
    $entityManager->flush();
    return $this->redirectToRoute('app_cart_index');
}


}
