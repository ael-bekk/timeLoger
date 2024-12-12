<?php


namespace App\Tests\Controller;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractApiTest extends ApiTestCase
{

    protected Client $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * @template T of object
     * @psalm-param class-string<T> $persistentObject
     * @psalm-return ObjectRepository<T>
     */
    protected function getRepository(string $persistentObject, string $persistentManagerName = null): ObjectRepository
    {
        if ($this->client->getContainer() === null) {
            $this->fail("Couldn't get container");
        }
        /**
         * @var ManagerRegistry|null $doctrine
         */
        $doctrine = $this->client->getContainer()->get('doctrine');
        if ($doctrine === null) {
            $this->fail("Couldn't get doctrine");
        }
        return $doctrine->getRepository($persistentObject, $persistentManagerName);
    }

    protected function getEntityManager(): ObjectManager
    {
        if ($this->client->getContainer() === null) {
            $this->fail("Couldn't get container");
        }
        /**
         * @var ManagerRegistry|null $doctrine
         */
        $doctrine = $this->client->getContainer()->get('doctrine');
        if ($doctrine === null) {
            $this->fail("Couldn't get doctrine");
        }
        return $doctrine->getManager();
    }

    protected function getIri(object $object): string
    {
        if ($this->client->getContainer() === null) {
            $this->fail("Couldn't get container");
        }
        /**
         * @var IriConverterInterface|null $iriConverter
         */
        $iriConverter = static::getContainer()->get('api_platform.iri_converter');
        if ($iriConverter === null) {
            $this->fail("Couldn't get IRI Converter");
        }
        return $iriConverter->getIriFromItem($object);
    }

    /**
     * @param array<int, object> $objects
     * @return string[]
     */
    protected function getIriStrings(array $objects): array
    {
        $iris = [];
        foreach ($objects as $obj) {
            $iris[] = $this->getIri($obj);
        }
        return $iris;
    }

    /**
     * @param mixed[] $expected
     * @param mixed[] $array
     * @param string|null $message
     */
    protected function assertArrayContainsAtLeastAnElement(array $expected, array $array, string $message = null): void
    {
        $found = false;
        foreach ($expected as $item) {
            if (in_array($item, $array)) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, $message ?? "Couldn't find any of the elements in array");
    }

    protected function tearDown(): void
    {
        // Reset rate limit
        /**
         * @var ?RedisAdapter $cacheAdapter
         */
        $cacheAdapter = self::getContainer()->get('cache.rate_limiter');
        if ($cacheAdapter === null) {
            $this->fail("Failed getting rate limiter cache");
        }
        $cacheAdapter->clear();
        parent::tearDown();
        // Remove client to save memory
        unset($this->client);
    }
}
