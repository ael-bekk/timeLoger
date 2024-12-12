<?php


namespace App\Tests\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\TokenAuthenticator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class TokenAuthenticatorTest extends TestCase
{
    private TokenAuthenticator $tokenAuthenticator;
    private User|null $user;

    protected function setUp(): void
    {
        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->willReturnCallback(function () {
            return $this->user;
        });
        $this->tokenAuthenticator = new TokenAuthenticator($userRepo);
    }

    /**
     * @param array<string, string> $headers
     */
    private function mockRequest(array $headers = []): Request
    {
        $headersBag = new HeaderBag($headers);
        $request = $this->createMock(Request::class);
        $request->headers = $headersBag;
        return $request;
    }

    public function testSupports(): void
    {
        $request = $this->mockRequest(['Authorization' => 'Token']);
        $this->assertTrue($this->tokenAuthenticator->supports($request));

        $request = $this->mockRequest();
        $this->assertFalse($this->tokenAuthenticator->supports($request));
    }

    public function testAuthenticateWorks(): void
    {
        $this->user = new User();
        $this->user->setUsername("a_user");

        $request = $this->mockRequest(['Authorization' => "existing_token"]);

        $passport = $this->tokenAuthenticator->authenticate($request);

        $this->assertEquals($this->user, $passport->getUser());
    }

    /**
     * @dataProvider invalidAuthentication
     */
    public function testAuthenticateDoesntWork(string|null $token, string $exceptionMessage): void
    {
        $this->user = null;

        $headers = [];
        if ($token !== null) {
            $headers = ["Authorization" => $token];
        }
        $request = $this->mockRequest($headers);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage($exceptionMessage);
        $this->tokenAuthenticator->authenticate($request);
    }

    public function testOnAuthenticationSuccess(): void
    {
        $request = $this->mockRequest([]);
        $token = $this->createMock(TokenInterface::class);

        $this->assertNull($this->tokenAuthenticator->onAuthenticationSuccess($request, $token, "main"));
    }

    public function testOnAuthenticationFailure(): void
    {
        $request = $this->mockRequest([]);
        $exception = new AuthenticationException("The message goes here");

        /**
         * @var JsonResponse $response
         */
        $response = $this->tokenAuthenticator->onAuthenticationFailure($request, $exception);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals('{"message":"An authentication exception occurred."}', $response->getContent());
    }

    /**
     * @return array<string, array<int, string|null>>
     */
    public function invalidAuthentication(): array
    {
        return [
            'no token' => [
                null,
                "No token provided",
            ],
            'invalid token' => [
                "some_token",
                "Invalid token",
            ],
        ];
    }
}
