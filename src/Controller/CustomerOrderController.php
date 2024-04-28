<?php

namespace App\Controller;
use App\Service\MailService;
use App\Entity\CustomerOrder;
use App\Entity\WhatsappNotif;
use App\Form\CustomerOrderType;
use App\Repository\CustomerOrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Cart;
use App\Entity\Orders;
use App\Repository\ProductsRepository;;
#[Route('/customer/order')]
class CustomerOrderController extends AbstractController
{
    #[Route('show/{id}', name: 'app_customer_order_show', methods: ['GET'])]
    public function show(CustomerOrder $customerOrder): Response
    {
        return $this->render('customer_order/show.html.twig', [
            'customer_order' => $customerOrder,
        ]);
    }
    
    #[Route('/', name: 'app_customer_order_index', methods: ['GET'])]
    public function index(CustomerOrderRepository $orderRepository): Response
    {
        return $this->render('customer_order/index.html.twig', [
            'customer_orders' => $orderRepository->findAll(),
        ]);
    }
    #[Route('/front/{id}', name: 'app_customer_order_front_index', methods: ['GET'])]
    public function index_front(int $id, Request $request, CustomerOrderRepository $customerOrderRepository): Response
    {
        $status = $request->query->get('Status'); // Get the status filter from the request query parameters

        // Determine the conditions based on the selected status filter
        $conditions = [];
        switch ($status) {
            case 'In progress...':
                $conditions = ['userId' => $id, 'Status' => 'In progress...'];
                break;
            case 'Delivered':
                $conditions = ['userId' => $id, 'Status' => 'Delivered'];
                break;
            case 'Old':
                $conditions = ['userId' => $id, 'Status' => 'Declined'];
                break;
            default:
                $conditions = ['userId' => $id];
                break;
        }

        // Fetch customer orders based on the conditions
        $customerOrders = $customerOrderRepository->findBy($conditions);

        return $this->render('customer_order/front_office/index.html.twig', [
            'customer_orders' => $customerOrders,
            'id' => $id, // Pass the 'id' variable to the Twig template
        ]);
    }
    #[Route('/new', name: 'app_customer_order_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ProductsRepository $productsRepository): Response
    {
        $customerOrder = new CustomerOrder();
        $customerOrder->setOrderDate(new \DateTime());
        $customerOrder->setStatus('In progress...');

        $form = $this->createForm(CustomerOrderType::class, $customerOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($customerOrder);
            $entityManager->flush();

            return $this->redirectToRoute('app_customer_order_index', [], Response::HTTP_SEE_OTHER);
        }

        // Fetch available products to populate the form
        $availableProducts = $productsRepository->findAll();

        return $this->renderForm('customer_order/new.html.twig', [
            'customer_order' => $customerOrder,
            'form' => $form,
            'availableProducts' => $availableProducts,
        ]);
    }
    #[Route('/{id}/edit', name: 'app_customer_order_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CustomerOrder $customerOrder, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CustomerOrderType::class, $customerOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_customer_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('customer_order/edit.html.twig', [
            'customer_order' => $customerOrder,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_customer_order_delete', methods: ['POST'])]
    public function delete(Request $request, CustomerOrder $customerOrder, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$customerOrder->getId(), $request->request->get('_token'))) {
            $entityManager->remove($customerOrder);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_customer_order_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/buy_all', name: 'app_cart_buy_all', methods: ['POST'])]
    public function buyAll(Request $request, EntityManagerInterface $entityManager, CustomerOrderRepository $orderRepository): Response
    {
 
        $userId = 1; // Set userId to 1
       
        $cartItems = $this->getDoctrine()->getRepository(Cart::class)->findBy(['userId' => $userId]);
       
        $order = new CustomerOrder();
        $order->setUserId($userId);
        $order->setOrderDate(new \DateTime());
       
        foreach ($cartItems as $item) {
            $order->addProduct([
                'productName' => $item->getProductName(),
                'productImage' => $item->getProductImage(),
                'quantity' => $item->getQuantity(),
                'price' => $item->getPrice(),
            ]);
           
            $entityManager->remove($item);
        }
       
        $entityManager->persist($order);
        $entityManager->flush();
    return $this->redirectToRoute('app_customer_order_show', ['id' => $order->getId()]);
    }

   

   
    #[Route('/{id}/toggle-status', name: 'app_toggle_status', methods: ['GET', 'POST'])]
    public function toggleStatus(CustomerOrder $customerOrder, EntityManagerInterface $entityManager, Request $request): Response
    {
        $currentStatus = $customerOrder->getStatus();
        
        // If the status is "Delivered" or "Declined", delete the order
        if ($currentStatus === 'Delivered' || $currentStatus === 'Declined') {
            $entityManager->remove($customerOrder);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_customer_order_index', [], Response::HTTP_SEE_OTHER);
        }
    
        // If the request method is POST, update the status
        if ($request->isMethod('POST')) {
            $newStatus = $request->request->get('status');
            $customerOrder->setStatus($newStatus);
            $entityManager->flush();

            // If the new status is "Delivered", send an email to the customer
            if ($newStatus === 'Delivered') {
                // Assuming you have a getter method getUser() to retrieve the associated User entity
                $user = $customerOrder->getUserId();

                // Make sure the user is not null and has an email
                if ($user instanceof Orders && $user->getEmail()) {
                    $customerEmail = $user->getEmail();
                    $emailSubject = 'Your order has been delivered';
                    $emailBody = 'Your order with ID ' . $customerOrder->getId() . ' has been delivered. Thank you for shopping with us.';

                    // Send email using the email service
                   // $this->emailService->sendEmail($customerEmail, $emailSubject, $emailBody);
                }
            }
    
            return $this->redirectToRoute('app_customer_order_index');
        }
    
        // Render the form to choose the new status
        return $this->render('customer_order/change_status.html.twig', [
            'customer_order' => $customerOrder,
        ]);
    }
}

