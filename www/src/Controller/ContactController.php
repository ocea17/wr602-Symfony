<?php

namespace App\Controller;

use App\Entity\UserContact;
use App\Repository\UserContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends AbstractController
{
    private UserContactRepository $contactRepository;
    private EntityManagerInterface $em;

    public function __construct(UserContactRepository $contactRepository, EntityManagerInterface $em)
    {
        $this->contactRepository = $contactRepository;
        $this->em = $em;
    }

    public function index(): Response
    {
        $user = $this->getUser();
        $contacts = $this->contactRepository->findBy(['user' => $user]);

        return $this->render('contact/index.html.twig', [
            'contacts' => $contacts,
        ]);
    }

    public function add(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $lastname = $request->request->get('lastname');
            $firstname = $request->request->get('firstname');
            $email = $request->request->get('email');

            if (!$lastname || !$firstname || !$email) {
                $this->addFlash('error', 'Tous les champs sont obligatoires.');
                return $this->redirectToRoute('app_contact_add');
            }

            $contact = new UserContact();
            $contact->setLastname($lastname);
            $contact->setFirstname($firstname);
            $contact->setEmail($email);
            $contact->setUser($this->getUser());

            $this->em->persist($contact);
            $this->em->flush();

            $this->addFlash('success', 'Contact ajouté avec succès !');
            return $this->redirectToRoute('app_contact_index');
        }

        return $this->render('contact/add.html.twig');
    }

    public function delete(int $id): Response
    {
        $contact = $this->contactRepository->find($id);

        if (!$contact) {
            throw $this->createNotFoundException('Contact non trouvé');
        }

        if ($contact->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce contact');
        }

        $this->em->remove($contact);
        $this->em->flush();

        $this->addFlash('success', 'Contact supprimé avec succès !');
        return $this->redirectToRoute('app_contact_index');
    }
}