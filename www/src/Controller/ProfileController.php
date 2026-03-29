<?php

namespace App\Controller;

use App\Repository\GenerationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[IsGranted('ROLE_USER')]
class ProfileController extends BaseController
{
    private GenerationRepository $generationRepository;
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        GenerationRepository $generationRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->generationRepository = $generationRepository;
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    public function index(): Response
    {
        $user = $this->getAuthenticatedUser();

        // Total des pdf
        $totalPdfs = $this->generationRepository->count(['user' => $user]);

        $todayStart = new \DateTime('today');
        $todayPdfs = $this->generationRepository->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->where('g.user = :user')
            ->andWhere('g.createdAt >= :today')
            ->setParameter('user', $user)
            ->setParameter('today', $todayStart)
            ->getQuery()
            ->getSingleScalarResult();

        $storedPdfs = $totalPdfs;

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'total_pdfs' => $totalPdfs,
            'today_pdfs' => $todayPdfs,
            'stored_pdfs' => $storedPdfs,
        ]);
    }
    public function updateColor(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $color = $request->request->get('color');

            $user = $this->getAuthenticatedUser();
            $user->setAvatarColor($color);

            $this->em->flush();

            $this->addFlash('success', 'Couleur modifiée avec succès !');
        }

        return $this->redirectToRoute('app_profile');
    }
    public function updateInfo(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $firstname = $request->request->get('firstname');
            $lastname = $request->request->get('lastname');
            $currentPassword = $request->request->get('current_password');
            $newPassword = $request->request->get('new_password');
            $newPasswordConfirm = $request->request->get('new_password_confirm');

            // Validation du nom et prénom
            if (!$firstname || !$lastname) {
                $this->addFlash('error', 'Le prénom et le nom sont obligatoires.');
                return $this->redirectToRoute('app_profile');
            }

            $user = $this->getAuthenticatedUser();
            $user->setFirstname($firstname);
            $user->setLastname($lastname);

            if ($currentPassword || $newPassword || $newPasswordConfirm) {
                // Vérifier que tous les champs mot de passe sont remplis
                if (!$currentPassword || !$newPassword || !$newPasswordConfirm) {
                    $this->addFlash('error', 'Pour changer le mot de passe, tous les champs sont obligatoires.');
                    return $this->redirectToRoute('app_profile');
                }

                // Vérifier que l'ancien mot de passe est correct
                if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
                    $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
                    return $this->redirectToRoute('app_profile');
                }

                // Vérifier la longueur du nouveau mot de passe
                if (strlen($newPassword) < 6) {
                    $this->addFlash('error', 'Le nouveau mot de passe doit contenir au moins 6 caractères.');
                    return $this->redirectToRoute('app_profile');
                }

                // Vérifier que les nouveaux mots de passe correspondent
                if ($newPassword !== $newPasswordConfirm) {
                    $this->addFlash('error', 'Les nouveaux mots de passe ne correspondent pas.');
                    return $this->redirectToRoute('app_profile');
                }

                // Hasher et sauvegarder le nouveau mot de passe
                $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);

                $this->addFlash('success', 'Vos informations et votre mot de passe ont été modifiés avec succès !');
            } else {
                $this->addFlash('success', 'Vos informations ont été modifiées avec succès !');
            }

            $this->em->flush();
        }

        return $this->redirectToRoute('app_profile');
    }
}