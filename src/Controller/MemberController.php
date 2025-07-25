<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\AddUserForm;
use App\Form\UpdateProfileForm;

#[IsGranted('ROLE_ADMIN', message: 'You are not allowed to perform this action.')]
final class MemberController extends AbstractController
{
    #[Route('/dashboard/member', name: 'app_member')]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10; // Number of users per page
        $users = $userRepository->findAllPaginated($page, $limit);
        $maxPages = ceil($users->count() / $limit);
        if ($page < 1 || $page > $maxPages) {
            $this->addFlash('error', 'Invalid page number.');
            return $this->redirectToRoute('app_member', ['page' => 1]);
        }

        return $this->render('member/index.html.twig', [
            'users' => $users,
            'MaxPages' => $maxPages,
            'page' => $page,
        ]);
    }

    
    #[Route('/dashboard/{id}/member', name: 'app_member_edit')]
    public function editOne(User $user, userRepository $userRepository, Request $request): Response
    {
        
        $form = $this->createForm(UpdateProfileForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);
            $this->addFlash('success', 'user updated!');
            return $this->redirectToRoute('app_member');
        }

        return $this->render('member/edit.html.twig', [
            'updateMemberForm' => $form
        ]);
    }


    #[Route('/dashboard/member/{id}/approuved', name: 'app_member_approuval')]
    public function approuvedUser(User $user, UserRepository $userRepository): Response
    {
        if($user->isApprouved() == true)
        {
            $user->setApprouved(false);
            $userRepository->save($user, true);
            $this->addFlash('warning', 'user unaprouved!');
            return $this->redirectToRoute('app_member');
        }

        $user->setApprouved(true);
        $userRepository->save($user, true);
        $this->addFlash('success', 'user approuved!');
        return $this->redirectToRoute('app_member');
    }

    #[Route('/dashboard/member/{id}/delete', name: 'app_member_delete')]
    public function deleteUser(User $user, UserRepository $userRepository): Response
    {
        // Prevent deletion of the currently logged-in user
        if ($user->getId() === $this->getUser()->getId()) {
            $this->addFlash('error', 'You cannot delete your own account.');
            return $this->redirectToRoute('app_member');
        }

        //have at least one admin
        $admins = $userRepository->findBy(['roles' => 'ROLE_ADMIN']);
        if (count($admins) <= 1 && in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->addFlash('error', 'You cannot delete the last admin user.');
            return $this->redirectToRoute('app_member');
        }

        $userRepository->remove($user, true);
        $this->addFlash('success', 'user deleted!');
        return $this->redirectToRoute('app_member');
    }
}
