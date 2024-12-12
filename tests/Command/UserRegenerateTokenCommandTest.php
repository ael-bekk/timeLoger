<?php

namespace App\Tests\Command;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class UserRegenerateTokenCommandTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    /**
     * @param array<string, mixed> $cmdInput
     * @dataProvider invalidArguments
     */
    public function testExecuteFails(array $cmdInput, string $expectedError): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find("app:user:regenerate-token");
        $commandTester = new CommandTester($command);

        $commandTester->execute($cmdInput);

        $this->assertEquals(1, $commandTester->getStatusCode());
        $this->assertStringContainsString($expectedError, $commandTester->getDisplay());
    }

    public function testExecute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find("app:user:regenerate-token");
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            "username" => "root",
        ]);

        $commandTester->assertCommandIsSuccessful();
    }

    /**
     * @return array<string, mixed>
     */
    public function invalidArguments(): array
    {
        return [
            "empty username" => [
                [
                    "username" => "",
                ],
                "You must pass a username"
            ],
            "non existing username" => [
                [
                    "username" => "non_existing_username",
                ],
                "User 'non_existing_username' not found"
            ],
        ];
    }
}
