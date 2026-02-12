<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PlanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        PlanRepository $planRepository
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $firstname = $request->request->get('firstname');
            $lastname = $request->request->get('lastname');

            // Validation simple
            if (!$email || !$password || !$firstname || !$lastname) {
                $this->addFlash('error', 'Tous les champs sont obligatoires.');
                return $this->render('registration/index.html.twig');
            }

            if (strlen($password) < 6) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 6 caractères.');
                return $this->render('registration/index.html.twig');
            }

            // Récupère le plan FREE par défaut
            $freePlan = $planRepository->findOneBy(['name' => 'FREE']);

            // Ici ça crée le nouvel utilisateur
            $user = new User();
            $user->setEmail($email);
            $user->setFirstname($firstname);
            $user->setLastname($lastname);
            $user->setRoles(['ROLE_USER']);
            $user->setPlan($freePlan);

            // Hash le mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/index.html.twig');
    }
}