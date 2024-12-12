<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;

abstract class AbstractUserCommand extends Command
{
    protected const TOKEN_LEN = 32;

    /**
     * Generates a random unique token
     */
    protected function generateUniqueToken(UserRepository $userRepository): string
    {
        do {
            $plainToken = bin2hex(openssl_random_pseudo_bytes(self::TOKEN_LEN));
            $hashedToken = hash(User::TOKEN_HASH, $plainToken);

            $foundUser = $userRepository->findOneBy(['hashedToken' => $hashedToken]);
        } while ($foundUser !== null);
        return $plainToken;
    }
}
