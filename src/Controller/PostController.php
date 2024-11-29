<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\PostType;
use App\Entity\Post;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class PostController extends AbstractController
{
    #[Route('/post', name: 'app_post')]
    public function index(): Response
    {
        return $this->render('post/index.html.twig', [
            'controller_name' => 'PostController',
        ]);
    }

    #[Route('/post/list', name: 'app_post_list')]
    public function list(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();
        return $this->render('post/list.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/post/create', name: 'app_post_create')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas les autorisations nécessaires pour accéder à cette page.');
            return $this->redirectToRoute('app_main');
        }
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Associer l'utilisateur connecté
            $user = $this->getUser();
            if ($user) {
                $post->setUser($user);
            }
            // Gestion de l'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('uploads_directory'), // Chemin de stockage
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gestion des erreurs
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }

                // Enregistrez le chemin de l'image dans l'entité
                $post->setPicture('/uploads/' . $newFilename);



                $entityManager->persist($post);
                $entityManager->flush();
    
                $this->addFlash('success', 'Publication ajoutée avec succès.');
                return $this->redirectToRoute('app_post_list');
            }
        }
    
        return $this->render('post/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    


    #[Route('/post/{id}', name: 'app_post_show')]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/post/delete/{id}', name: 'app_post_delete')]
    public function delete(Post $post, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas les autorisations nécessaires pour accéder à cette page.');
            return $this->redirectToRoute('app_main');
        }
        $entityManager->remove($post);
        $entityManager->flush();
        return $this->redirectToRoute('app_post_list');
    }

    #[Route('/post/edit/{id}', name: 'app_post_edit')]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas les autorisations nécessaires pour accéder à cette page.');
            return $this->redirectToRoute('app_main');
        }
        $form = $this->createForm(PostType::class, $post);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
    
            $this->addFlash('success', 'Le post a été mis à jour avec succès.');
    
            return $this->redirectToRoute('app_post_list');
        }
    
        return $this->render('post/edit.html.twig', [
            'postForm' => $form->createView(),
            'post' => $post,
        ]);
    }
}
