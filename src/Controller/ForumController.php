<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\Publication;
use App\Form\PublicationType;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\PublicationRepository;
use Symfony\Component\Mailer\Mailer;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;


class ForumController extends AbstractController
{
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    #[Route('/forum', name: 'app_forum')]
public function index(Request $request, PaginatorInterface $paginator): Response
{
    // Récupérer l'EntityManager
    $entityManager = $this->managerRegistry->getManager();
    
    // Récupérer toutes les publications depuis la base de données
    $query = $entityManager->getRepository(Publication::class)->createQueryBuilder('p')
        ->orderBy('p.datepublication', 'DESC')
        ->getQuery();
    
    // Paginer les résultats
    $publications = $paginator->paginate(
        $query, // Requête à paginer
        $request->query->getInt('page', 1), // Numéro de page par défaut
        2 // Nombre d'éléments par page
    );
    
    // Créer un tableau pour stocker les formulaires de commentaire
    $commentForms = [];
    
    // Créer un tableau pour stocker la moyenne des notes de chaque publication
    $averageRatings = [];
    
    // Pour chaque publication, créer un formulaire de commentaire
    foreach ($publications as $publication) {
        $commentaire = new Commentaire();
        
       
        $commentForm = $this->createForm(CommentaireType::class, $commentaire);
        $commentForms[$publication->getId()] = $commentForm->createView();
        
    
        // Calculer la moyenne des notes pour chaque publication
        $totalRating = 0;
        $commentaires = $publication->getCommentaires();
        foreach ($commentaires as $commentaire) {
            $totalRating += $commentaire->getNote();
        }
        $averageRatings[$publication->getId()] = count($commentaires) > 0 ? $totalRating / count($commentaires) : 0;
    }
    $form = $this->createForm(CommentaireType::class, $commentaire);
    
    return $this->render('forum/index.html.twig', [
        'publications' => $publications,
        'comment_forms' => $commentForms,
        'average_ratings' => $averageRatings,
        'form' => $form->createView(),
    ]);
}
    


    #[Route('/publication/new', name: 'publication_new')]
public function new(ManagerRegistry $manager, Request $request): Response
{
    $publication = new Publication();
    $form = $this->createForm(PublicationType::class, $publication);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        /** @var UploadedFile $imageFile */
        $imageFile = $form->get('image')->getData();

        // Gérer l'envoi du fichier
        if ($imageFile) {
            // Utiliser le nom d'origine du fichier
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            // Pour conserver l'extension du fichier
            $newFilename = $originalFilename.'.'.$imageFile->guessExtension();

            // Déplacer le fichier vers l'emplacement souhaité
            // Vous pouvez utiliser la méthode move() pour déplacer le fichier
            // vers un répertoire spécifique sur le serveur.
            // $imageFile->move($this->getParameter('images_directory'), $newFilename);

            // Mettre à jour l'entité avec le nom du fichier
            $publication->setImage($newFilename);
        }

        // Enregistrer l'entité dans la base de données
        $entityManager = $manager->getManager();
        $entityManager->persist($publication);
        $entityManager->flush();
        

        // Rediriger vers la page de forum après l'ajout
        return $this->redirectToRoute('my_publications');
    }

    return $this->render('addpublication.html.twig', [
        'form' => $form->createView(),
    ]);
}
#[Route('/publication/{id}/add-comment', name: 'add_comment')]
public function addCommentaire(ManagerRegistry $manager, Request $request, int $id,MailerInterface $mailer): Response
{
    // Récupérer l'EntityManager
    $entityManager = $manager->getManager();

    // Récupérer la publication correspondant à l'ID
    $publication = $entityManager->getRepository(Publication::class)->find($id);

    // Vérifier si la publication existe
    if (!$publication) {
        throw $this->createNotFoundException('La publication avec l\'ID ' . $id . ' n\'existe pas.');
    }

    // Créer une nouvelle instance de l'entité Commentaire
    $commentaire = new Commentaire();
    $commentaire->setNote(3);
    
    

    // Créer le formulaire à partir de CommentaireType
    $form = $this->createForm(CommentaireType::class, $commentaire);

    // Gérer la soumission du formulaire
    $form->handleRequest($request);


    // Vérifier si le formulaire a été soumis et si les champs sont vides
    if ($form->isSubmitted() && $form->isEmpty()) {
        // Si le formulaire est soumis mais vide, simplement retourner la vue avec le formulaire de commentaire ouvert
        // et les publications pour que l'utilisateur puisse le remplir
        $publications = $entityManager->getRepository(Publication::class)->findAll();
        

        return $this->render('forum/index.html.twig', [
            'form' => $form->createView(),
            'publications' => $publications,
        ]);
    }

    // Si le formulaire a été soumis et est valide, associer le commentaire à la publication et enregistrer dans la base de données
    if ($form->isSubmitted() && $form->isValid()) {
       
        
        
        $commentaire->setPublication($publication);
     

        // Enregistrer le commentaire dans la base de données
        $entityManager->persist($commentaire);
        $entityManager->flush();
        $this->sendEmail($mailer);

        // Rediriger vers la page d'accueil ou tout autre endroit approprié
        return $this->redirectToRoute('forum');
    }

    // Si le formulaire n'a pas encore été soumis, simplement afficher la vue avec le formulaire de commentaire et les publications
    $publications = $entityManager->getRepository(Publication::class)->findAll();

    return $this->render('forum/index.html.twig', [
        'form' => $form->createView(),
        'publications' => $publications,
    ]);
}

#[Route('/my-publications', name: 'my_publications')]
public function myPublications(ManagerRegistry $manager ): Response
{
    // Définir l'ID de l'utilisateur
    $userId = 1;

    // Récupérer les publications de l'utilisateur avec l'ID 1 depuis la base de données
    $entityManager = $manager->getManager();
    $publications = $entityManager->getRepository(Publication::class)->findBy(['iduser' => $userId]);

    // Rendre le template Twig et transmettre les publications
    return $this->render('mypublications.html.twig', [
        'publications' => $publications,
    ]);
}
#[Route('/publication/delete/{id}', name: 'delete_publication')]
    public function deletePublication(Request $request, int $id): RedirectResponse
    {
        // Récupérer l'EntityManager
        $entityManager = $this->managerRegistry->getManager();

        // Récupérer la publication à supprimer
        $publication = $entityManager->getRepository(Publication::class)->find($id);

        if (!$publication) {
            // Gérer le cas où la publication n'est pas trouvée
            throw $this->createNotFoundException('La publication avec l\'id ' . $id . ' n\'existe pas.');
        }

        // Supprimer la publication
        $entityManager->remove($publication);
        $entityManager->flush();
        
        

        // Rediriger vers la page principale du forum
        return $this->redirectToRoute('my_publications', ['suppression' => true]);
    }
    #[Route('/publication/edit/{id}', name: 'edit_publication')]
    public function editPublication(Request $request, int $id): Response
    {
        // Récupérer l'EntityManager
        $entityManager = $this->managerRegistry->getManager();
    
        // Récupérer la publication à modifier
        $publication = $entityManager->getRepository(Publication::class)->find($id);
    
        if (!$publication) {
            // Gérer le cas où la publication n'est pas trouvée
            throw $this->createNotFoundException('La publication avec l\'id ' . $id . ' n\'existe pas.');
        }
    
        // Sauvegarder le chemin de l'ancienne image
        $ancienneImage = $publication->getImage();
    
        // Créer un formulaire de modification pour la publication
        $form = $this->createForm(PublicationType::class, $publication);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer la nouvelle image du formulaire
            $nouvelleImage = $form->get('image')->getData();
    
            if ($nouvelleImage) {
                // Gérer le téléchargement de la nouvelle image
                $nomFichier = pathinfo($nouvelleImage->getClientOriginalName(), PATHINFO_FILENAME);
                $nouveauNomFichier = $nomFichier . '.'   . $nouvelleImage->guessExtension();
    
                // Déplacer la nouvelle image vers le répertoire souhaité
                $nouvelleImage->move($this->getParameter('images_directory'), $nouveauNomFichier);
    
                // Mettre à jour l'entité de la publication avec le chemin de la nouvelle image
                $publication->setImage($nouveauNomFichier);
    
                // Supprimer l'ancienne image si nécessaire (optionnel)
                if ($ancienneImage) {
                    unlink($this->getParameter('images_directory') . '/' . $ancienneImage);
                }
            }
    
            // Enregistrer les modifications dans la base de données
            $entityManager->flush();
    
            // Rediriger vers la page principale du forum
            return $this->redirectToRoute('app_forum');
        }
    
        // Afficher le formulaire de modification de la publication
        return $this->render('editpublication.html.twig', [
            'form' => $form->createView(),
            'ancienneImage' => $ancienneImage, // Passer l'URL de l'ancienne image au template
        ]);
    }
    
    #[Route('/delete_comment/{id}', name: 'delete_comment')]
    public function deleteComment(ManagerRegistry $manager, int $id): RedirectResponse
    {
        // Récupérer l'EntityManager
        $entityManager = $manager->getManager();

        // Récupérer le commentaire à supprimer
        $commentaire = $entityManager->getRepository(Commentaire::class)->find($id);

        if (!$commentaire) {
            // Gérer le cas où le commentaire n'est pas trouvé
            throw $this->createNotFoundException('Le commentaire avec l\'id ' . $id . ' n\'existe pas.');
        }

        // Supprimer le commentaire
        $entityManager->remove($commentaire);
        $entityManager->flush();

        // Rediriger vers la page précédente ou toute autre page appropriée
        return $this->redirectToRoute('app_forum');
    }
    /**
     * @Route("/edit_comment/{id}", name="edit_comment")
     */
    public function editComment(ManagerRegistry $manager ,Request $request, $id): Response
    {
        $entityManager = $manager->getManager();
        $commentaire = $entityManager->getRepository(Commentaire::class)->find($id);

        // Vérifiez si le commentaire existe
        if (!$commentaire) {
            throw $this->createNotFoundException('Le commentaire avec l\'identifiant ' . $id . ' n\'existe pas.');
        }

        // Créez un formulaire pour modifier le commentaire
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        // Vérifiez si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrez les modifications dans la base de données
            $entityManager->flush();

            // Redirigez l'utilisateur vers la page appropriée ou affichez un message de succès
            return $this->redirectToRoute('forum'); 
        }

        // Affichez le formulaire pour modifier le commentaire
        return $this->render('add_comment.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/email', name: 'app_email')]
public function sendEmail(MailerInterface $mailer): Response
{
    $transport=Transport::fromDsn('smtp://davincisdata@gmail.com:vjyyzltfspajsbpf@smtp.gmail.com:587');
    $mailer = new Mailer($transport);
    
    // Construire le contenu personnalisé du mail
    $mailContent = "Un utilisateur a commenté votre publication.";

    // Créer l'email
    $email = (new Email())
        ->from('davincisdata@gmail.com')
        ->to('aminehamrouni10@gmail.com')
        ->subject('Notification de commentaire')
        ->text($mailContent)
        ->html('<p>' . $mailContent . '</p>');

    // Envoyer l'email
    $mailer->send($email);

    // Retourner une réponse
    return $this->render('email/index.html.twig', [
        'controller_name' => 'EmailController',
    ]);
}
    
    

}
