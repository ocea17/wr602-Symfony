<?php

namespace App\Command;

use App\Repository\PlanRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:change-plan',
    description: 'Change user plan',
)]
class ChangePlanCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private PlanRepository $planRepository,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('plan', InputArgument::REQUIRED, 'Plan name (FREE, BASIC, PREMIUM)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        $planName = strtoupper($input->getArgument('plan'));

        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (! $user) {
            $io->error("User not found: $email");

            return Command::FAILURE;
        }

        $plan = $this->planRepository->findOneBy(['name' => $planName]);
        if (! $plan) {
            $io->error("Plan not found: $planName");

            return Command::FAILURE;
        }

        $user->setPlan($plan);
        $this->em->flush();

        $io->success("User $email updated to $planName plan!");

        return Command::SUCCESS;
    }
}
