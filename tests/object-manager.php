<?php

use App\Kernel;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__ . '/../.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
/**
 * @var Registry| null $doctrine
 */
$doctrine = $kernel->getContainer()->get('doctrine');
if ($doctrine === null) {
    throw new LogicException('Doctrine is missing. Try running "composer require doctrine/doctrine-bundle".');
}
return $doctrine->getManager();
