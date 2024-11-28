<?php

namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\CommentType;
use App\Entity\Comment;
use App\Repository\CommentRepository;


class CommentController extends AbstractController
{
    #[Route('/comment', name: 'app_comment')]
    public function index(): Response
    {
        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }

    #[Route('/post/{postId}/comment', name: 'comment_form')]
    public function form(PostRepository $postRepository, $postId, Request $request, EntityManagerInterface $entityManager)
    {
        $post = $postRepository->find($postId);
    
        if (!$post) {
            throw $this->createNotFoundException('Le post n\'existe pas.');
        }
    
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);
    
        $commentForm->handleRequest($request);
    
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            // Lier le commentaire au post
            $comment->setPost($post);
            $comment->setUser($this->getUser());
            $createdAt = $comment->getCreateAt();

            if ($createdAt === null) {
                // Si createdAt n'est pas défini, lever une exception ou définir une valeur par défaut
                throw new \Exception('La date de création (createdAt) doit être renseignée.');
            }
    
            // Sauvegarder le commentaire
            $entityManager->persist($comment);
            $entityManager->flush();
    
            $this->addFlash('success', 'Votre commentaire a été ajouté.');
    
            return $this->redirectToRoute('app_post_list', ['postId' => $post->getId()]);
        }
    
        return $this->render('comment/add.html.twig', [
            'post' => $post,
            'commentForm' => $commentForm->createView(),
        ]);
    }
    
}