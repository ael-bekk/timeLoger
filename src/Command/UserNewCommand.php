<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:new',
    description: 'Creates a new user and generate a token',
)]
class UserNewCommand extends AbstractUserCommand
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
            )
            ->addOption(
                "role",
                null,
                InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED,
                "Roles to assign to this token"
            )
            ->addOption(
                "update",
                null,
                InputOption::VALUE_NONE,
                "Update existing user if any"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = (string) $input->getArgument('username');
        $update = (bool) $input->getOption('update');

        if (empty($username)) {
            $io->error("You must pass a username");
            return Command::FAILURE;
        }
        // Make sure the username is unique
        $user = $this->userRepository->findOneBy(['username' => $username]);
        if ($user !== null && !$update) {
            $io->error("The username '{$username}' is already used.");
            return Command::FAILURE;
        }

        // Validate roles
        $providedRoles = (array) $input->getOption("role");
        if (empty($providedRoles)) {
            $io->error("You must pass at least one role using the --role flag");
            return Command::FAILURE;
        }
        foreach ($providedRoles as $providedRole) {
            if (!in_array($providedRole, User::AVAILABLE_ROLES)) {
                $io->error("The role '{$providedRole}' is invalid, options are: ".implode(",", User::AVAILABLE_ROLES));
                return Command::FAILURE;
            }
        }
        // Create a new user
        $plainToken = null;
        if ($user === null) {
            $plainToken = $this->generateUniqueToken($this->userRepository);
            $user = new User();
            $user->setUsername($username)
                ->setHashedToken(hash(User::TOKEN_HASH, $plainToken));
        }
        $user->setUsername($username)
            ->setRoles($providedRoles)
            ->setIsDisabled(false);
        $this->em->persist($user);
        $this->em->flush();

        // Output the raw token
        if ($update) {
            $io->writeln("User updated!");
            return Command::SUCCESS;
        }

        $io->note("Please make sure you store the token somewhere safe, you can not recover it if lost.");
        $io->block([
            "Your token is: ".$plainToken,
            "Assigned roles are: ".implode(", ", $user->getRoles()),
        ]);

        return Command::SUCCESS;
    }
}
