<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
use App\Repository\PublicationRepository;
class ForumadminController extends AbstractController
{
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    #[Route('/forumadmin', name: 'app_forumadmin')]
    public function index(Request $request): Response
{
    // Récupérer l'EntityManager
    $entityManager = $this->managerRegistry->getManager();

    // Récupérer toutes les publications depuis la base de données
    $publications = $entityManager->getRepository(Publication::class)->findAll();

    // Créer un tableau pour stocker les formulaires de commentaire
    $commentForms = [];

    // Pour chaque publication, créer un formulaire de commentaire
    foreach ($publications as $publication) {
        $commentaire = new Commentaire();
        $commentForm = $this->createForm(CommentaireType::class, $commentaire);
        $commentForms[$publication->getId()] = $commentForm->createView();
    }

    return $this->render('forumadmin/index.html.twig', [
        'publications' => $publications,
        'comment_forms' => $commentForms,
    ]);
}
#[Route('/publication/newadmin', name: 'publication_newadmin')]
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
        return $this->redirectToRoute('my_publicationsadmin');
    }

    return $this->render('addpublicationadmin.html.twig', [
        'form' => $form->createView(),
    ]);
}
#[Route('/publication/{id}/add-comment', name: 'add_comment')]
public function addCommentaire(ManagerRegistry $manager, Request $request, int $id): Response
{
    // Récupérer la publication correspondant à l'ID
    $entityManager = $manager->getManager();
    $publication = $entityManager->getRepository(Publication::class)->find($id);

    // Vérifier si la publication existe
    if (!$publication) {
        throw $this->createNotFoundException('La publication avec l\'ID ' . $id . ' n\'existe pas.');
    }

    // Créer une nouvelle instance de l'entité Commentaire
    $commentaire = new Commentaire();

    // Créer le formulaire à partir de CommentaireType
    $form = $this->createForm(CommentaireType::class, $commentaire);

    // Gérer la soumission du formulaire
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Associer le commentaire à la publication si la publication existe
        if ($publication) {
            $commentaire->setPublication($publication);

            // Enregistrer le commentaire dans la base de données
            $entityManager->persist($commentaire);
            $entityManager->flush();

            // Rediriger vers la page d'accueil ou tout autre endroit approprié
            return $this->redirectToRoute('forum_admin');
        }
    }

    // Si le formulaire n'est pas encore soumis ou s'il contient des erreurs, afficher à nouveau le formulaire
    return $this->render('addcommentaire.html.twig', [
        'form' => $form->createView(),
    ]);
}
#[Route('/my-publicationsadmin', name: 'my_publicationsadmin')]
public function myPublications(ManagerRegistry $manager ): Response
{
    // Définir l'ID de l'utilisateur
    $userId = 1;

    // Récupérer les publications de l'utilisateur avec l'ID 1 depuis la base de données
    $entityManager = $manager->getManager();
    $publications = $entityManager->getRepository(Publication::class)->findBy(['iduser' => $userId]);

    // Rendre le template Twig et transmettre les publications
    return $this->render('mypublicationsadmin.html.twig', [
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
        return $this->redirectToRoute('my_publicationsadmin');
    }
    #[Route('/publication/edit/admin/{id}', name: 'edit_publicationadmin')]
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
            return $this->redirectToRoute('forum_admin');
        }
    
        // Afficher le formulaire de modification de la publication
        return $this->render('editpublicationadmin.html.twig', [
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
        return $this->redirectToRoute('forumadmin');
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
            return $this->redirectToRoute('forumadmin'); // Remplacez 'forum_index' par le nom de la route appropriée
        }

        // Affichez le formulaire pour modifier le commentaire
        return $this->render('add_comment.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/stats", name="stats")
     */
    public function statistiques(PublicationRepository $publicationRepository): Response
    {
        $publications = $publicationRepository->findAll();
        $data = [];

        // Récupérer le nombre de commentaires pour chaque publication
        foreach ($publications as $publication) {
            $data[] = [
                'title' => $publication->getTitre(),
                'comment_count' => count($publication->getCommentaires())
            ];
        }

        return $this->render('Forumadmin/chart.html.twig', [
            'data' => json_encode($data)
        ]);
    }
    #[Route('/', name: 'search', methods: ['POST'])]
    public function search(Request $request, PublicationRepository $publicationRepository): Response
    {
        
        $requestData = json_decode($request->getContent(), true);
        $searchValue = $requestData['search'] ?? ''; 
    
      
        if (empty($searchValue)) {
           
            $publications = $publicationRepository->findAll();
        } else {
           
            $publications = $publicationRepository->createQueryBuilder('e')
                ->where('e.titre LIKE :searchValue')
                ->setParameter('searchValue', '%' . $searchValue . '%')
                ->getQuery()
                ->getResult();
        }
    
      
        return $this->render('forumadmin/publicationsearched.html.twig', [
            'publications' => $publications, 
        ]);
    
        }
 
}
