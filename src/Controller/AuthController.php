<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if user is already logged in, redirect them based on role is desirable, 
        // but for now let's just show the login page or maybe the dashboard if they click login again.
        // For simplicity, we just render the login page, but usually you check $this->getUser().

        if ($this->getUser()) {
             if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
                 return $this->redirectToRoute('admin_dashboard');
             }
             return $this->redirectToRoute('student_dashboard');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/register', name: 'register')]
    public function register(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $userPasswordHasher, \Doctrine\ORM\EntityManagerInterface $entityManager): Response
    {
        $user = new \App\Entity\User();
        $form = $this->createForm(\App\Form\RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setRoles(['ROLE_STUDENT']);

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('login');
        }

        return $this->render('auth/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/forgot-password', name: 'forgot_password')]
    public function forgotPassword(): Response
    {
        return $this->render('auth/forgot_password.html.twig');
    }
}