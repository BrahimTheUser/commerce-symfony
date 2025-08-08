<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
class UserController extends AbstractController
{
    #[Route('/users', name: 'app_user')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/users/{id}/to/editor', name: 'app_user_to_editor')]
    public function changeRole(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setRoles(["ROLE_EDITOR", "ROLE_USER"]);
        $entityManager->flush();
        $this->addFlash('success', 'le role éditeur a été ajouté à votre utilisateur');
        return $this->redirectToRoute('app_user');
    }

    #[Route('/users/new', name: 'app_user_new')]
    public function newUser(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/users/{id}/delete', name: 'app_user_delete')]
    public function deleteUser(UserRepository $userRepository, $id, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($id);
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('danger', "l'utilisatteur a été supprimé");

        return $this->redirectToRoute('app_user');
    }
}
