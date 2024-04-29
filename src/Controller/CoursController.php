<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Form\Cours1Type;
use App\Repository\CoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
#[Route('/cours')]
class CoursController extends AbstractController
{
    #[Route('/', name: 'app_cours_index', methods: ['GET'])]
    public function index(CoursRepository $coursRepository): Response
    {
        return $this->render('cours/index.html.twig', [
            'cours' => $coursRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_cours_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $cour = new Cours();
        $cour->setDatePub(new \DateTime('today'));
        $form = $this->createForm(Cours1Type::class, $cour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            if ($image) {
                // Gérer l'upload de l'image
                $fileName = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move(
                    $this->getParameter('images_directory'),
                    $fileName
                );
                $cour->setImage($fileName);
            }
            $entityManager->persist($cour);
            $entityManager->flush();
            $this->sendEmail($mailer);
            return $this->redirectToRoute('app_cours_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cours/new.html.twig', [
            'cour' => $cour,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cours_show', methods: ['GET'])]
    public function show(Cours $cour): Response
    {
        return $this->render('cours/show.html.twig', [
            'cour' => $cour,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_cours_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cours $cour, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Cours1Type::class, $cour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            if ($image) {
                // Gérer l'upload de l'image
                $fileName = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move(
                    $this->getParameter('images_directory'),
                    $fileName
                );
                $cour->setImage($fileName);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_cours_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cours/edit.html.twig', [
            'cour' => $cour,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cours_delete', methods: ['POST'])]
    public function delete(Request $request, Cours $cour, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cour->getId(), $request->request->get('_token'))) {
            $entityManager->remove($cour);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cours_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/', name: 'search', methods: ['POST'])]
    public function search(Request $request, CoursRepository $coursRepository,PaginatorInterface $paginator): Response
    {
        
        $requestData = json_decode($request->getContent(), true);
        $searchValue = $requestData['search'] ?? ''; 
    
      
        if (empty($searchValue)) {
           
        $cour = $coursRepository->findAll(); // Récupérer tous les cours
    
        } else {
           
            $cour = $coursRepository->createQueryBuilder('e')
                ->where('e.nom LIKE :searchValue')  
                ->setParameter('searchValue', '%' . $searchValue . '%')
                ->getQuery()
                ->getResult();
        }
    
      
        return $this->render('cours/search.html.twig', [
            'cours' => $cour, 
        ]);
    
        }
    
          /**
     * @Route("/stats", name="stats")
     */
    public function statistiques(CoursRepository $coursRepository): Response
    {
        $cours = $coursRepository->findAll();
        $data = [];

        // Récupérer le nombre de commentaires pour chaque cours
        foreach ($cours as $cours) {
            $data[] = [
                'nom' => $cours->getnom(),
                'comment_count' => count($cours->getavis())
            ];
        }

        return $this->render('cours/stat.html.twig', [
            'data' => json_encode($data)
        ]);
    }
   #[Route('/email', name: 'app_email')]
            public function sendEmail(MailerInterface $mailer)
            {
                $transport=Transport::fromDsn('smtp://davincisdata@gmail.com:vjyyzltfspajsbpf@smtp.gmail.com:587');
                $mailer = new Mailer($transport);
                
                // Construire le contenu personnalisé du mail
                $mailContent = "Un nouveau cours a été ajouté";
            
                // Créer l'email
                $email = (new Email())
                    ->from('davincisdata@gmail.com')
                    ->to('mouadh.fersi@esprit.tn')
                    ->subject('Notification de commentaire')
                    ->text($mailContent)
                    ->html('<p>' . $mailContent . '</p>');
            
                // Envoyer l'email
                $mailer->send($email);
            
                
                
            }
           
}
