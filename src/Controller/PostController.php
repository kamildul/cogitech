<?php

namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PostController extends AbstractController
{

    #[Route('/lista', name: 'app_post')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $posts = $entityManager->getRepository(Post::class)->findAll();
        return $this->render('post/list.html.twig', [
            'controller_name' => 'PostController',
            'posts' => $posts,
        ]);
    }

    #[Route('/lista/usun/{id}', name: 'delete_post')]
    public function delete(EntityManagerInterface $entityManager, Post $post): RedirectResponse
    {
        $entityManager->remove($post);
        $entityManager->flush();

        $this->addFlash('success', 'Post został usunięty.');
        return $this->redirectToRoute('app_post');
    }

}
