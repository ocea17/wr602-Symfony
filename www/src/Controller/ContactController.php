<?php
namespace App\Controller;

use App\Entity\UserContact;
use App\Form\UserContactType;
use App\Repository\UserContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ContactController extends BaseController
{
    private EntityManagerInterface $em;
    private UserContactRepository $contactRepository;

    public function __construct(
        EntityManagerInterface $em,
        UserContactRepository $contactRepository
    ) {
        $this->em = $em;
        $this->contactRepository = $contactRepository;
    }

    public function index(): Response
    {
        $user = $this->getAuthenticatedUser();
        $contacts = $this->contactRepository->findBy(
            ['user' => $user],
            ['lastname' => 'ASC']
        );

        return $this->render('contact/index.html.twig', [
            'contacts' => $contacts,
        ]);
    }

    public function new(Request $request): Response
    {
        $contact = new UserContact();
        $form = $this->createForm(UserContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getAuthenticatedUser();
            $contact->setUser($user);

            $this->em->persist($contact);
            $this->em->flush();

            $this->addFlash('success', 'Contact ajouté avec succés !');

            // Redirection conditionnelle
            $redirect = $request->query->get('redirect');
            if ($redirect) {
                return $this->redirectToRoute($redirect);
            }

            return $this->redirectToRoute('app_contacts_index');
        }

        return $this->render('contact/new.html.twig', [
            'contact' => $contact,
            'form' => $form,
        ]);
    }

    public function edit(Request $request, UserContact $contact): Response
    {
        if ($contact->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(UserContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Contact modifi� avec succ�s !');
            return $this->redirectToRoute('app_contacts_index');
        }

        return $this->render('contact/edit.html.twig', [
            'contact' => $contact,
            'form' => $form,
        ]);
    }

    public function delete(Request $request, UserContact $contact): Response
    {
        if ($contact->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$contact->getId(), $request->request->get('_token'))) {
            $this->em->remove($contact);
            $this->em->flush();
            $this->addFlash('success', 'Contact supprimé avec succés !');
        }

        return $this->redirectToRoute('app_contacts_index');
    }
}
