<?php

namespace App\Controller;

use App\Repository\PlanRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class HomePublicController extends AbstractController
{
    private PlanRepository $planRepository;

    public function __construct(PlanRepository $planRepository)
    {
        $this->planRepository = $planRepository;
    }

    public function index(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // Récupère tous les plans actifs
        $plans = $this->planRepository->findBy(['active' => true], ['id' => 'ASC']);

        return $this->render('home_public/index.html.twig', [
            'plans' => $plans,
        ]);
    }
}