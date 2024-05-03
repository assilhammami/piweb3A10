<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\Helpers;
use App\Service\UploaderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;



class UserController extends AbstractController
{
    public function __construct(private LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    #[Route('/home', name: 'home_page')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var User $user */
        $user = $this->getUser();
        return match($user->isVerified()){
            true => $this->render('home.html.twig'),
            false => $this->render('user/please-verify-your-email.html.twig')
        };
        }
    
    
    
    #[Route('/admin', name: 'app_admin')]
    public function admin(): Response
    {  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var User $user */
        $user = $this->getUser();
        return match($user->isVerified()){
            true => $this->render('user/list.html.twig'),
            false => $this->render('user/please-verify-your-email.html.twig')
        };
       
    }
    #[Route('/user', name: 'app_user')]
    public function user(): Response
    {  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var User $user */
        $user = $this->getUser();
        return match($user->isVerified()){
            true => $this->render('home.html.twig'),
            false => $this->render('user/please-verify-your-email.html.twig')
        };
        
    }
    #[Route('/artiste', name: 'app_artiste')]
    public function artiste(): Response
    {$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var User $user */
        $user = $this->getUser();
        return match($user->isVerified()){
            true => $this->render('home.html.twig'),
            false => $this->render('user/please-verify-your-email.html.twig')
        };
       
    }
    #[Route('/user/getall', name: 'get_all')]
    public function getAllUsers(UserRepository $repo)
    {
        $users = $repo->findall();
        $users = $repo->findAll();
        return $this->render('user/list.html.twig', ['users' => $users]);
    }
    #[Route('/user/get/{id}', name: 'getbyid')]
    public function getUserId(UserRepository $repo, $id)
    {
        $user = $repo->find($id);
        return $this->render('user/details.html.twig', ['user' => $user]);
    }
    #[Route('/user/delete/{id}', name: 'deletebyid')]
    public function deleteUserbyId(ManagerRegistry $manager, UserRepository $repo, $id)
    {
        $user = $repo->find($id);
        $manager->getManager()->remove($user);
        $manager->getManager()->flush();
        return $this->redirectToRoute('get_all');
    }
    #[Route('/user/add', name: 'user_add', methods: ['GET', 'POST'])]
public function addUser(ManagerRegistry $manager, Request $req,Helpers $helper,UploaderService $uploader)
{
    
    $user = new User();
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($req);

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var UploadedFile  $photo */
        $photo = $form['photo_de_profile']->getData();
        dump($photo); // Check if the file is being correctly retrieved

        if ($photo) {
            $directory=$this->getParameter('photos_directory');

            $user->setPhotoDeProfile($uploader->uploadFile($photo,$directory));
        }

        // Persist the user entity to the database
        $manager->getManager()->persist($user);
        $manager->getManager()->flush();
        $this->addFlash('success',$helper->UserCreated());

        // Redirect to a page after successful submission
        return $this->redirectToRoute('get_all');
    }

    // If the form is not submitted or is not valid, render the form template
    return $this->renderForm('user/add.html.twig', ['f' => $form]);
}
    #[Route('/user/update/{id}', name: 'updateform')]
    public function updateUser($id, ManagerRegistry $manager, UserRepository $repo, Request $req,Helpers $helper,UploaderService $uploader, UserPasswordHasherInterface $userPasswordHasher)
    {
        $user = $repo->find($id);
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($req);
        /*if ($form->isSubmitted()) {
            $manager->getManager()->persist($user);
            $manager->getManager()->flush();
            return $this->redirectToRoute('get_all');
        }
        return $this->renderForm('user/add.html.twig', ['f' => $form]);
    }*/if ($form->isSubmitted() && $form->isValid()) {
        /** @var UploadedFile  $photo */
        $photo = $form['photo_de_profile']->getData();
        dump($photo); // Check if the file is being correctly retrieved

        if ($photo) {
            $directory=$this->getParameter('photos_directory');

            $user->setPhotoDeProfile($uploader->uploadFile($photo,$directory));
        }
        $plainPassword = $form->get('plainPassword')->getData();
if ($plainPassword) {
    $hashedPassword = $userPasswordHasher->hashPassword($user, $plainPassword);
    $user->setPassword($hashedPassword);
    $user->eraseCredentials();
}
        // Persist the user entity to the database
        $manager->getManager()->persist($user);
        $manager->getManager()->flush();
       

        // Redirect to a page after successful submission
       
    }
    return $this->renderForm('user/myprofile.html.twig', ['f' => $form]);}
    
    #[Route('/user/deactivate/{id}', name: 'deactivate')]
    public function Deactivate($id, ManagerRegistry $manager, UserRepository $repo, Request $req)
    {
        $user = $repo->find($id);
        $user->setActive(false);
        $manager->getManager()->persist($user);
        $manager->getManager()->flush();
        return $this->redirectToRoute('get_all');
    }


    #[Route('/user/activate/{id}', name: 'activate')]
    public function Activate($id, ManagerRegistry $manager, UserRepository $repo, Request $req)
    {
        $user = $repo->find($id);
        $user->setActive(true);
        $manager->getManager()->persist($user);
        $manager->getManager()->flush();
        return $this->redirectToRoute('get_all');
    }
    #[Route('/template', name: 'template')]
    public function template(): Response
    {
        return $this->render('template.html.twig');
    }
}