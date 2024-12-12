<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:regenerate-token',
    description: 'Regenerate token for a user',
)]
class UserRegenerateTokenCommand extends AbstractUserCommand
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'A unique username to identify the User (a User can be a person or a machine)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = (string) $input->getArgument('username');

        if (empty($username)) {
            $io->error("You must pass a username");
            return Command::FAILURE;
        }

        $user = $this->userRepository->findOneBy(['username' => $username]);
        if ($user === null) {
            $io->error("User '{$username}' not found");
            return Command::FAILURE;
        }

        $plainToken = $this->generateUniqueToken($this->userRepository);
        $user->setHashedToken(hash(User::TOKEN_HASH, $plainToken));

        $this->em->persist($user);
        $this->em->flush();

        // Output the raw token
        $io->note("Please make sure you store the token somewhere safe, you can not recover it if lost.");
        $io->block([
            "Your token is: ".$plainToken,
            "Assigned roles are: ".implode(", ", $user->getRoles()),
        ]);

        return Command::SUCCESS;
    }
}
