<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AddUserForm;
use App\Security\AppCustomAuthenticator;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class AddUserController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    
    #[Route('/dashboard/member/add', name: 'app_member_add')]
    #[IsGranted('ROLE_ADMIN', message: 'You are not allowed to perform this action.')]
    public function register(Request $request, 
    UserAuthenticatorInterface $userAuthenticator,
    UserPasswordHasherInterface $userPasswordHasher,
    AppCustomAuthenticator $authenticator, 
    UserRepository $userRepository): Response
    {
        $user = new user();
        $form = $this->createForm(AddUserForm::class, $user);
        $form->handleRequest($request);

      
        if ($form->isSubmitted() && $form->isValid()) {
            
            // encode the plain password
            $user->setRoles($form->get('roles')->getData());
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            }

            $userRepository->save($user, true);

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@vgcreator.fr', 'Confirmation de votre email'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm  back your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            $this->addFlash('success', 'User added successfully! A confirmation email has been sent to the user.');
            return $this->redirectToRoute('app_dashbaord');
        }

        return $this->render('add_user/index.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

   
}