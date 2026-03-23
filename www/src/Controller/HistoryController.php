<?php

namespace App\Controller;

use App\Repository\GenerationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class HistoryController extends AbstractController
{
    private GenerationRepository $generationRepository;
    private EntityManagerInterface $em;

    public function __construct(
        GenerationRepository $generationRepository,
        EntityManagerInterface $em
    ) {
        $this->generationRepository = $generationRepository;
        $this->em = $em;
    }

    public function index(Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $user = $this->getUser();

        // Total de générations
        $totalGenerations = $this->generationRepository->count(['user' => $user]);

        // Générations paginées
        $generations = $this->generationRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC'],
            $limit,
            $offset
        );

        $totalPages = (int) ceil($totalGenerations / $limit);

        return $this->render('history/index.html.twig', [
            'generations' => $generations,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalGenerations' => $totalGenerations,
        ]);
    }
    public function delete(int $id, Request $request): Response
    {
        $generation = $this->generationRepository->find($id);

        if (!$generation || $generation->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('PDF introuvable');
        }

        // Vérification du token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete' . $id, $token)) {
            $this->addFlash('error', 'Token de sécurité invalide');
            return $this->redirectToRoute('app_history');
        }

        // Supprime le fichier physique
        $filePath = __DIR__ . '/../../public/' . $generation->getFile();
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Supprime de la BDD
        $this->em->remove($generation);
        $this->em->flush();

        $this->addFlash('success', 'PDF supprimé avec succès');
        return $this->redirectToRoute('app_history');
    }
    public function download(int $id): Response
    {
        $generation = $this->generationRepository->find($id);

        if (!$generation) {
            throw $this->createNotFoundException('PDF non trouvé');
        }

        if ($generation->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce PDF');
        }

        $filepath = __DIR__ . '/../../public/' . $generation->getFile();

        if (!file_exists($filepath)) {
            $this->addFlash('error', 'Le fichier PDF n\'existe plus sur le serveur');
            return $this->redirectToRoute('app_history');
        }

        return $this->file($filepath);
    }
}