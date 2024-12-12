<?php

namespace App\Tests\Command;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

// TODO: test that the command actually creates/updates user
class UserNewCommandTest extends KernelTestCase
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

        $command = $application->find("app:user:new");
        $commandTester = new CommandTester($command);

        $commandTester->execute($cmdInput);

        $this->assertEquals(1, $commandTester->getStatusCode());
        $this->assertStringContainsString($expectedError, $commandTester->getDisplay());
    }

    public function testExecute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find("app:user:new");
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            "username" => "spoody",
            "--role" => ["ROLE_SESSION_READ"],
        ]);

        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString(
            "Assigned roles are: ROLE_SESSION_READ, ROLE_USER",
            $commandTester->getDisplay()
        );
    }

    public function testExecuteWithUpdate(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find("app:user:new");
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            "username" => "root",
            "--role" => ["ROLE_SESSION_READ"],
            "--update" => true,
        ]);

        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString(
            "User updated!",
            $commandTester->getDisplay()
        );
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
            "taken username" => [
                [
                    // From the fixtures
                    "username" => "root",
                ],
                "The username 'root' is already used."
            ],
            "no roles" => [
                [
                    "username" => "new_username",
                ],
                "You must pass at least one role using the --role flag"
            ],
            "invalid role" => [
                [
                    "username" => "new_username",
                    "--role" => ["ROLE_SESSION_READ", "ROLE_THAT_IS_NOT_VALID"]
                ],
                "The role 'ROLE_THAT_IS_NOT_VALID' is invalid, options are:"
            ],
        ];
    }
}
