<?php

namespace App\Controller;

use App\Entity\MediaTricks;
use App\Entity\Tricks;
use App\Form\TricksFormType;
use App\Repository\TricksRepository;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tricks', name: 'tricks_')]
class TricksController extends AbstractController
{
    #[Route('/', name: 'all_tricks')]
    public function index(TricksRepository $tricksRepository): Response
    {
        // On recupère toute les données pour l'injecter à la vue
        $tricks = $tricksRepository->findBy([], ['tricks_created_at' => 'DESC']);
        return $this->render('tricks/index.html.twig', compact('tricks'));
    }

    #[Route('/readTricks/{id}', name: 'read_tricks')]
    public function readTricks(TricksRepository $tricksRepository, int $id): Response
    {
        // On recupère toute le tricks selon son id pour l'injecter à la vue
        $tricksSelected = $tricksRepository->findOneBy(['id' => $id]);
        return $this->render('tricks/read.html.twig', compact('tricksSelected'));
    }

    #[Route('/add/picture', name: 'add_picture_tricks', methods: ['POST'])]
    public function addPicture(Request $request, MediaTricks $mediaTrick, PictureService $pictureService): JsonResponse
    {
        //On récupère le contenu de la requête
        $data = json_decode($request->getContent(), true);

        // On récupère le nom de l'image
        /*$mediaName = $mediaTrick->setMediaName();


        if ($pictureService->add($mediaName, 'media_tricks', 300, 300)) {
            // Si la requête est effectué on renvoie un code 200

            return new JsonResponse(['success' => true], 200);
        }

        //La supréssion a échoué*/
        return new JsonResponse(['message' => $data], 200);
    }

    #[Route('/addTricks', name: 'add_new_tricks')]
    public function addTricks(Request $request, EntityManagerInterface $em, PictureService $pictureService): Response
    {
        //On crée un "nouveau tricks"
        $tricks = new Tricks();

        //ajout de l'id de l'utilisateur qui post dans le tricks
        $user = $this->getUser();
        $tricks->setUser($user);

        //On crée le formulaire
        $tricksForm = $this->createForm(TricksFormType::class, $tricks);

        // On traite la requête du formulaire
        $tricksForm->handleRequest($request);

        //On vérifie si le formulaire est soumis et valide
        if ($tricksForm->isSubmitted() && $tricksForm->isValid()) {
            //On récupère les images
            $mediaTricks = $tricksForm->get('media_tricks')->getData();

//            $images = $tricksForm->get('images')->getData();

//            dd($tricksForm, $mediaTricks);

            foreach($mediaTricks as $mediaTrick) {
                //On définit le dossier de destination
                $folder = "media_tricks";

                //On appelle le service d'ajout
                $file = $pictureService->add($mediaTrick, $folder, 300, 300);

                $mediaEntity = new MediaTricks();
                $mediaEntity->setMediaName($file);
                $tricks->addMediaTrick($mediaEntity);

            }

            //On Stock en base de donnée
            $em->persist($tricks);
            $em->flush();
            
            //On envoie un message flash 
            $this->addFlash('success','tricks ajouté avec succès');

            //On redirige 
            return $this->redirectToRoute('main');
        }

        return $this->render('tricks/add.html.twig', [
            'tricksForm' => $tricksForm->createView()
        ]);
    }

    #[Route('/updateTricks/{id}', name: 'update_tricks')]
    public function updateTricks(Tricks $tricks, Request $request, EntityManagerInterface $em, PictureService $pictureService): Response
    {
        //On crée le formulaire
        $tricksForm = $this->createForm(TricksFormType::class, $tricks);

        // On traite la requête du formulaire
        $tricksForm->handleRequest($request);

        //On vérifie si le formulaire est soumis et valide
        if ($tricksForm->isSubmitted() && $tricksForm->isValid()) {
            //On récupère les images
            $mediaTricks = $tricksForm->get('media_tricks')->getData();

            foreach($mediaTricks as $mediaTrick) {
                //On définit le dossier de destination
                $folder = "media_tricks";

                //On appelle le service d'ajout
                $file = $pictureService->add($mediaTrick,$folder, 300, 300);

                $mediaEntity = new MediaTricks();
                $mediaEntity->setMediaName($file);
                $tricks->addMediaTrick($mediaEntity);

            }

            //On Stock en base de donnée
            $em->persist($tricks);
            $em->flush();
            
            //On envoie un message flash 
            $this->addFlash('success','tricks ajouté avec succès');

            //On redirige 
            return $this->redirectToRoute('main');
        }

        return $this->render('tricks/update.html.twig',  [
            'tricksForm' => $tricksForm->createView(),
            'tricks' => $tricks
        ]);
    }

    #[Route('/delete/image/{id}', name: 'delete_image', methods: ['DELETE'])]
    public function deleteImage(MediaTricks $mediaTrick ,Request $request, EntityManagerInterface $em, PictureService $pictureService): JsonResponse
    {
        
        //On récupère le contenu de la requête
        $data = json_decode($request->getContent(), true);

        if ($this->isCsrfTokenValid('delete' . $mediaTrick->getId(), $data['_token'])) {
            // Le token csrf est valide 
            // On récupère le nom de l'image
            $mediaName = $mediaTrick->getMediaName();

            if ($pictureService->delete($mediaName, 'media_tricks', 300, 300)) {
                // On supprime l'image de la base de données
                $em->remove($mediaTrick);
                $em->flush();

                return new JsonResponse(['success' => true], 200);
            }

            //La supréssion a échoué
            return new JsonResponse(['error' => 'Erreur de suppression', $mediaName], 400);
        }
        
        return new JsonResponse(['error' => 'Token invalide'], 400);
    }

    #[Route('/deleteTricks/{id}', name: 'delete_tricks')]
    public function deleteTricks(TricksRepository $tricksRepository,EntityManagerInterface $entityManager, int $id, PictureService $pictureService)
    {

        $tricks = $tricksRepository->find($id);
        $mediaTricks = $tricks->getMediaTricks();
        
        foreach($mediaTricks as $media) {
            // On boucle sur la collection getMediaTricks pour supprimer les photos du serveur
            $pictureService->delete($media->getMediaName(), 'media_tricks', 300, 300);
        }
        
        if (!$tricks) {
            throw $this->createNotFoundException('Pas de tricks trouvé avec l\'id:'.$id);
        }
        
        $entityManager->remove($tricks);
        $entityManager->flush();
    
        return $this->render('main/index.html.twig');
        
    }
   
}
