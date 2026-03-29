<?php

namespace App\Controller;

use App\Repository\GenerationRepository;
use App\Repository\UserContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class HistoryController extends BaseController
{
    private GenerationRepository $generationRepository;
    private EntityManagerInterface $em;
    private UserContactRepository $contactRepository;

    public function __construct(
        GenerationRepository $generationRepository,
        EntityManagerInterface $em,
        UserContactRepository $contactRepository
    ) {
        $this->generationRepository = $generationRepository;
        $this->em = $em;
        $this->contactRepository = $contactRepository;
    }

    public function index(Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $user = $this->getAuthenticatedUser();

        $totalGenerations = $this->generationRepository->count(['user' => $user]);

        $generations = $this->generationRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC'],
            $limit,
            $offset
        );

        $totalPages = (int) ceil($totalGenerations / $limit);

        $contacts = $this->contactRepository->findBy(['user' => $user]);

        return $this->render('history/index.html.twig', [
            'generations' => $generations,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalGenerations' => $totalGenerations,
            'contacts' => $contacts,
        ]);
    }

    public function delete(int $id, Request $request): Response
    {
        $generation = $this->generationRepository->find($id);

        if (! $generation || $generation->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('PDF introuvable');
        }

        $token = $request->request->get('_token');
        if (! $this->isCsrfTokenValid('delete'.$id, $token)) {
            $this->addFlash('error', 'Token de sécurité invalide');

            return $this->redirectToRoute('app_history');
        }

        $filePath = __DIR__.'/../../public/'.$generation->getFile();
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->em->remove($generation);
        $this->em->flush();

        $this->addFlash('success', 'PDF supprimé avec succès');

        return $this->redirectToRoute('app_history');
    }

    public function download(int $id): Response
    {
        $generation = $this->generationRepository->find($id);

        if (! $generation) {
            throw $this->createNotFoundException('PDF non trouvé');
        }

        if ($generation->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce PDF');
        }

        $filepath = __DIR__.'/../../public/'.$generation->getFile();

        if (! file_exists($filepath)) {
            $this->addFlash('error', 'Le fichier PDF n\'existe plus sur le serveur');

            return $this->redirectToRoute('app_history');
        }

        $type = $generation->getType() ?: 'document';
        $customName = 'pdfgenerator_'.$type.'.pdf';

        return $this->file($filepath, $customName);
    }

    public function send(int $id, Request $request, MailerInterface $mailer): Response
    {
        $generation = $this->generationRepository->find($id);

        if (! $generation || $generation->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('PDF introuvable');
        }

        // Vérification CSRF
        if (! $this->isCsrfTokenValid('send_pdf', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide');

            return $this->redirectToRoute('app_history');
        }

        // Récupère les contacts sélectionnés
        $contactIds = $request->request->all('contacts');

        if (empty($contactIds)) {
            $this->addFlash('error', 'Veuillez sélectionner au moins un destinataire');

            return $this->redirectToRoute('app_history');
        }

        $contacts = $this->contactRepository->findBy(['id' => $contactIds, 'user' => $this->getUser()]);

        if (empty($contacts)) {
            $this->addFlash('error', 'Aucun contact valide sélectionné');

            return $this->redirectToRoute('app_history');
        }

        // Message personnalisé
        $customMessage = $request->request->get('message', '');

        // Chemin du PDF
        $filepath = $this->getParameter('kernel.project_dir').'/public/'.$generation->getFile();

        if (! file_exists($filepath)) {
            $this->addFlash('error', 'Le fichier PDF n\'existe plus');

            return $this->redirectToRoute('app_history');
        }

        // Envoie l'email à chaque contact
        $sentCount = 0;
        foreach ($contacts as $contact) {
            try {
                $email = (new Email())
                    ->from('oce.leite1712@gmail.com')
                    ->to($contact->getEmail())
                    ->subject('PDF : '.($generation->getCustomName() ?? 'Document'))
                    ->html(
                        '<p>Bonjour '.htmlspecialchars($contact->getFirstname()).',</p>'.
                        '<p>Vous trouverez ci-joint le document PDF.</p>'.
                        ($customMessage ? '<p><em>'.nl2br(htmlspecialchars($customMessage)).'</em></p>' : '').
                        '<p>Cordialement,<br>'.htmlspecialchars($this->getUser()->getEmail()).'</p>'
                    )
                    ->attachFromPath($filepath);

                $mailer->send($email);
                ++$sentCount;
            } catch (\Exception $e) {
                continue;
            }
        }

        if ($sentCount > 0) {
            $this->addFlash('success', "PDF envoyé à {$sentCount} destinataire(s)");
        } else {
            $this->addFlash('error', 'Erreur lors de l\'envoi des emails');
        }

        return $this->redirectToRoute('app_history');
    }
}
