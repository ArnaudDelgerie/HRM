<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Workflow\WorkflowInterface;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_auth_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser() instanceof UserInterface) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('auth/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error'         => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/registration/completion/{token}', name: 'app_auth_registration_completion')]
    public function registrationCompletion(
        string $token,
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        WorkflowInterface $userStateMachine,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        if ($this->getUser() instanceof User) {
            return $this->redirectToRoute('app_home');
        }

        /** @var ?User $user */
        $user = $userRepository->findOneBy(['validationToken' => $token]);
        if (!$user instanceof User || !$userStateMachine->can($user, 'enable')) {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(UserPasswordType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);

            $user
                ->setValidationToken(null)
                ->setPassword($hashedPassword);
            $userStateMachine->apply($user, 'enable');

            $em->flush();

            $this->addFlash('success', 'auth.registration_completion.message.success');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('/auth/registration_completion.html.twig', [
            'form' => $form,
        ]);
    }
}
