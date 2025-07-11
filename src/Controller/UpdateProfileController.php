<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\UpdateProfileForm;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\UpdateProfilePictureType;
#use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UpdateProfileController extends AbstractController
{
    /*
    #[Route('/update/profile/picture', name: 'app_update_profile_picture')]
    public function updatePicture(Request $request, UserRepository $userRepository): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(UpdateProfilePictureType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $profileImageFile = $user->getImageFile();
            $profileImageFile = $form->get('imageFile')->getData();

            if ($profileImageFile) {
                // dd($profileImageFile);
                $user->setProfileImageName($profileImageFile);
                // $user->setImageName($profileImageFile->getClientOriginalName());
            }

            $userRepository->save($user, true);
            return $this->redirectToRoute('admin_app_update_profile_picture');
        }

        return $this->renderForm('back/update_profile_picture/index.html.twig', [
            'updateProfilePictureForm' => $form
        ]);
    }
    */


    #[Route('/dashboard/update/profile', name: 'app_profile_update')]
    public function edit(UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher,  Request $request): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(UpdateProfileForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /*
            # Update the user entity with the new data
            $user->setEmail($form->get('email')->getData());
            $user->setFirstName($form->get('firstName')->getData());
            $user->setLastName($form->get('lastName')->getData());
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPlainPassword($plainPassword);
            }
            */

            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            }

            // Save the updated user entity
            $userRepository->save($user, true);

            $this->addFlash('success', 'Profile updated!');
            return $this->redirectToRoute('app_dashbaord');
        }

        return $this->render('update_profile/index.html.twig', [
            'updateProfileForm' => $form
        ]);
    }
}