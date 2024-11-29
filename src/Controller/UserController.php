<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/user/admin', name: 'app_user_admin')]
    public function admin(): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas les autorisations nécessaires pour accéder à cette page.');
            return $this->redirectToRoute('app_main');
        }
        return $this->render('user/admin.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/user/list', name: 'app_user_list')]
    public function list(UserRepository $userRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas les autorisations nécessaires pour accéder à cette page.');
            return $this->redirectToRoute('app_main');
        }
        $users = $userRepository->findAll();
        return $this->render('user/list.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/delete/{id}', name: 'app_user_delete')]
    public function delete(User $user, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas les autorisations nécessaires pour accéder à cette page.');
            return $this->redirectToRoute('app_main');
        }
        $entityManager->remove($user);
        $posts = $user->getPost();
        foreach ($posts as $post) {
            $entityManager->remove($post);
        }
        $comments = $user->getComment();
        foreach ($comments as $comment) {
            $entityManager->remove($comment);
        }
        $entityManager->flush();
        return $this->redirectToRoute('app_user_list');
    }

    #[Route('/user/role/{id}', name: 'app_user_role')]
    public function role(User $user, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas les autorisations nécessaires pour accéder à cette page.');
            return $this->redirectToRoute('app_main');
        }
        $roles = $user->getRoles();
        $user->setRoles(['ROLE_USER']);

        $entityManager->flush();
        return $this->redirectToRoute('app_user_list');
    }

    #[Route('/user/unrole/{id}', name: 'app_user_unrole')]
    public function unrole(User $user, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas les autorisations nécessaires pour accéder à cette page.');
            return $this->redirectToRoute('app_main');
        }
        $roles = $user->getRoles();
        $user->setRoles([]);

        $entityManager->flush();
        return $this->redirectToRoute('app_user_list');
    }
}
