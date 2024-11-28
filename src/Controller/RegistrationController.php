<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        $file = $request->files->get('profile_picture');
        if ($form->isSubmitted() && $form->isValid()) {
            $profilePicture = $form->get('profile_picture')->getData();
            if ($profilePicture) {
                // Gérer le téléchargement de l'image (par exemple, déplacer le fichier)
                $newFilename = uniqid() . '.' . $profilePicture->guessExtension();
                $profilePicture->move($this->getParameter('uploads/profile_pictures'), $newFilename);
        
                // Enregistrer l'URL du fichier dans la base de données
                $user->setProfilePicture($newFilename);
            } else {
                // Si aucune image n'est envoyée, on laisse la valeur à null
                $user->setProfilePicture("");
            }
        
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            // do anything else you need here, like send an email
            // Récupérer le fichier de l'image
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
