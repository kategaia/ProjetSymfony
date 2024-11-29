<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CategoryRepository;
use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(): Response
    {
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
        ]);
    }

    #[Route('/category/create', name: 'app_category_create')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas les autorisations nécessaires pour accéder à cette page.');
            return $this->redirectToRoute('app_main');
        }
        $category = new category();
        $form = $this->createForm(categoryType::class, $category);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();
            return $this-> redirectToRoute('app_category', ['id' => $category->getId()]);
            }

        return $this->render('category/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/category/edit/{id}', name: 'app_category_edit')]
    public function edit($id): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas les autorisations nécessaires pour accéder à cette page.');
            return $this->redirectToRoute('app_main');
        }
        return $this->render('category/edit.html.twig', [
            'controller_name' => 'CategoryController',
        ]);
    }

    #[Route('/category/delete/{id}', name: 'app_category_delete')]
    public function delete($id): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas les autorisations nécessaires pour accéder à cette page.');
            return $this->redirectToRoute('app_main');
        }
        return $this->render('category/delete.html.twig', [
            'controller_name' => 'CategoryController',
        ]);
    }

    #[Route('/category/list', name: 'app_category_list')]
    public function list(categoryRepository $categoryRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas les autorisations nécessaires pour accéder à cette page.');
            return $this->redirectToRoute('app_main');
        }
        $categories = $categoryRepository->findAll();
        return $this->render('category/list.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/category/{id}', name: 'app_category_show')]
    public function show(Category $category, categoryRepository $categoryRepository, $id): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas les autorisations nécessaires pour accéder à cette page.');
            return $this->redirectToRoute('app_main');
        }
        $category = $categoryRepository->find($id);

        if (!$category) {
            throw $this->createNotFoundException('La catégorie demandée est introuvable.');
        }
        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }



}
