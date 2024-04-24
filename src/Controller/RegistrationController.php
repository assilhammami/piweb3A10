<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Security\UserAuthAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use App\Service\Helpers;
use App\Service\UploaderService;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager, Helpers $helper, UploaderService $uploader,UserAuthenticatorInterface $userAuthenticator, UserAuthAuthenticator $authenticator): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPlainPassword(
                $form->get('plainPassword')->getData()
            );
        
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $user->getPlainPassword()
                )
            );
                
        $photo = $form['photo_de_profile']->getData();
        if ($photo) {
            $directory=$this->getParameter('photos_directory');
    
            $user->setPhotoDeProfile($uploader->uploadFile($photo,$directory));
        }
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            $this->addFlash('success',$helper->UserCreated());
            
            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('davincisdata@gmail.com'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('/registration/confirmation_email.html.twig')
                    
            );

            // do anything else you need here, like send an email

            return $security->login($user, UserAuthAuthenticator::class, 'main');
           
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_login');
    }
    #[Route('/email')]
    public function sendEmail(): Response
    {  $transport=Transport::fromDsn('smtp://davincisdata@gmail.com:vjyyzltfspajsbpf@smtp.gmail.com:587');
        $mailer = new Mailer($transport);
        $email = (new Email())
            ->from('davincisdata@gmail.com')
            ->to('oualiyoussef9@gmail.com')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);

        return $this->render('email.html.twig', [
            'controller_name' => 'EmailController',]);
    }
}