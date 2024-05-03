<?php

namespace App\Controller;

use App\Service\SmsGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Form\ChangePasswordFormType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry;

class SmsController extends AbstractController
{
   
    //La vue du formulaire d'envoie du sms
    #[Route('/sms', name: 'sms')]
    public function index(): Response
    {
        return $this->render('reset_password/sms.html.twig', [
            'smsSent' => false,
            'errorMessage' => null,  // Add errorMessage
        ]);
    }

    //Gestion de l'envoie du sms
    #[Route('/sendSms', name: 'send_sms', methods: ['POST', 'GET'])]
public function sendSms(Request $request, SmsGenerator $smsGenerator, EntityManagerInterface $em, SessionInterface $session): Response
{
    $number = $request->request->get('number');
    $errorMessage = null;

    if (!preg_match("/^[4592][0-9]{7}$/", $number)) {
        $errorMessage = 'Invalid phone number.';
    } else {
        $user = $em->getRepository(User::class)->findOneBy(['num_telephone' => $number]);

        if (!$user) {
            $errorMessage = 'Phone number not found.';
        } else {
            $number1 = '+216' . $number;  // Add the country code for Tunisia
            $verificationCode=$smsGenerator->sendSms($number1);
            $session->set('verificationCode', $verificationCode);
            $session->set('number', $number);  // Store the user in the session
            return $this->redirectToRoute('verify');
        }
    }

    return $this->render('reset_password/sms.html.twig', [
        'smsSent' => $errorMessage === null,
        'errorMessage' => $errorMessage
    ]);
}
#[Route('/verify', name: 'verify', methods: ['POST', 'GET'])]
public function verify(Request $request, SessionInterface $session): Response
{
    $enteredCode = (string) $request->request->get('code');
$storedCode = (string) $session->get('verificationCode');
    

    if ($enteredCode === $storedCode) {
        // The codes match, redirect to the password reset page
        return $this->redirectToRoute('reset_password_sms');
    } else {
        // The codes don't match, display an error message
        return $this->render('reset_password/sms_verify_code.html.twig', [
            'errorMessage' => 'The verification code is incorrect.','enteredCode' => $enteredCode,
            'storedCode' => $storedCode
        ]);
    }
}
#[Route('/reset-password-sms', name: 'reset_password_sms', methods: ['GET', 'POST'])]
public function resetPassword(Request $request, SessionInterface $session, UserPasswordHasherInterface $passwordEncoder ,ManagerRegistry $manager,EntityManagerInterface $em): Response
{
    $number = (int) $session->get('number');
    $user = $em->getRepository(User::class)->findOneBy(['num_telephone' => $number]);
    if (!$user) {
        // No user in session, redirect to login page
        return $this->redirectToRoute('app_login');
    }

    $form = $this->createForm(ChangePasswordFormType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Encode and set the new password
        $encodedPassword = $passwordEncoder->hashPassword(
            $user,
            $form->get('plainPassword')->getData()
        );
        $user->setPassword($encodedPassword);

        // Save the new password
        $manager->getManager()->persist($user);
        $manager->getManager()->flush();

        // Redirect to the login page
        return $this->redirectToRoute('app_login');
    }

    return $this->render('reset_password/sms_reset_password.html.twig', [
        'form' => $form->createView(),
    ]);
}}