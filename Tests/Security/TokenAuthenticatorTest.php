<?php

namespace tests\App\Security;

use App\Tests\Mock\Entity\User;
use App\Tests\Mock\JWTEncoderTrait;
use EncreInformatique\UserApiBundle\Security\TokenAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\LcobucciJWTEncoder;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\LcobucciJWSProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\RawKeyLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class TokenAuthenticatorTest extends TestCase
{
    use JWTEncoderTrait;

    /**
     * @test
     * @group Security
     */
    public function authenticationFailure()
    {
        $jwtEncoder = new LcobucciJWTEncoder($this->createMock(LcobucciJWSProvider::class));

        $authenticator = new TokenAuthenticator($jwtEncoder);
        $authenticationFailure = $authenticator->onAuthenticationFailure(new Request(), new AuthenticationException('exception'));

        $this->assertTrue($authenticationFailure instanceof JsonResponse);
        $this->assertEquals($authenticationFailure->getStatusCode(), Response::HTTP_FORBIDDEN);
    }

    /**
     * @test
     * @group Security
     */
    public function authenticationSuccess()
    {
        $jwtEncoder = new LcobucciJWTEncoder($this->createMock(LcobucciJWSProvider::class));
        $token = $this->createMock(TokenInterface::class);

        $authenticator = new TokenAuthenticator($jwtEncoder);
        $authenticationSuccess = $authenticator->onAuthenticationSuccess(new Request(), $token, 'providerKey');

        $this->assertTrue(null === $authenticationSuccess);
    }

    /**
     * @test
     * @group Security
     */
    public function started()
    {
        $jwtEncoder = new LcobucciJWTEncoder($this->createMock(LcobucciJWSProvider::class));

        $authenticator = new TokenAuthenticator($jwtEncoder);
        $authenticationStart = $authenticator->start(new Request(), new AuthenticationException('exception'));

        $this->assertTrue($authenticationStart instanceof JsonResponse);
        $this->assertEquals($authenticationStart->getStatusCode(), Response::HTTP_UNAUTHORIZED);
        $this->assertEquals($authenticationStart->getContent(), '{}');
    }

    /**
     * @test
     * @group Security
     */
    public function emptyCredentials()
    {
        $jwtEncoder = new LcobucciJWTEncoder($this->createMock(LcobucciJWSProvider::class));

        $requestStack = new Request();

        $authenticator = new TokenAuthenticator($jwtEncoder);
        $credentials = $authenticator->getCredentials($requestStack);

        $this->assertFalse($credentials);
    }

    /**
     * @test
     * @group Security
     */
    public function checkCredentials()
    {
        $jwtEncoder = new LcobucciJWTEncoder($this->createMock(LcobucciJWSProvider::class));

        $requestStack = new Request();

        $authenticator = new TokenAuthenticator($jwtEncoder);
        $credentials = $authenticator->checkCredentials($requestStack, new User());

        $this->assertTrue($credentials);
    }

    /**
     * @test
     * @group Security
     */
    public function gettingCredentialsFromBearer()
    {
        $jwtEncoder = new LcobucciJWTEncoder($this->createMock(LcobucciJWSProvider::class));

        $requestStack = new Request([], [], [], [], [], ['HTTP_Authorization' => 'Bearer my-bearer-token']);

        $authenticator = new TokenAuthenticator($jwtEncoder);
        $credentials = $authenticator->getCredentials($requestStack);

        $this->assertTrue(is_string($credentials));
        $this->assertEquals('my-bearer-token', $credentials);
    }

//    /**
//     * @test
//     * @group Security
//     */
//    public function gettingCredentialsFromXAtuhToken()
//    {
//        $jwtEncoder = new LcobucciJWTEncoder($this->createMock(LcobucciJWSProvider::class));
//
//        $requestStack = new Request([], [], [], [], [], ['HTTP_X-Auth-Token' => 'my-private-token']);
//
//        $authenticator = new TokenAuthenticator($jwtEncoder);
//        $credentials = $authenticator->getCredentials($requestStack);
//
//        $this->assertTrue(is_string($credentials));
//        $this->assertEquals('my-private-token', $credentials);
//    }

    /**
     * @test
     * @group Security
     */
    public function cannotDecodeCredentials()
    {
        $jwtEncoder = $this->mockJWTEncoder();

        $mockUserProvider = $this->createMock(UserProviderInterface::class);

        $authenticator = new TokenAuthenticator($jwtEncoder);
        $credentials = $authenticator->getUser(new \stdClass(), $mockUserProvider);

        $this->assertTrue(null === $credentials);
    }

    /**
     * @test
     * @group Security
     */
    public function canDecodeCredentials()
    {
        $jwtEncoder = new LcobucciJWTEncoder(
            new LcobucciJWSProvider(
                new RawKeyLoader(__DIR__.'/../../config/jwt/public.pem'),
                'openssl',
                'HS256',
                300,
                0
            )
        );

        $mockUserProvider = $this->createMock(UserProviderInterface::class);
        $mockUserProvider->expects($this->once())
            ->method('loadUserByUsername')
            ->with('my-username')
            ->willReturn('my-username');

        $authenticator = new TokenAuthenticator($jwtEncoder);
        $credentials = $authenticator->getUser($jwtEncoder->encode(['username' => 'my-username']), $mockUserProvider);

        $this->assertEquals('my-username', $credentials);
    }

    /**
     * @test
     * @group Security
     */
    public function supportsRememberMe()
    {
        $jwtEncoder = new LcobucciJWTEncoder($this->createMock(LcobucciJWSProvider::class));

        $authenticator = new TokenAuthenticator($jwtEncoder);
        $this->assertFalse($authenticator->supportsRememberMe());
    }

    /**
     * @test
     * @group Security
     */
    public function supported()
    {
        $jwtEncoder = new LcobucciJWTEncoder($this->createMock(LcobucciJWSProvider::class));
        //$token = $this->createMock(TokenInterface::class);

        $authenticator = new TokenAuthenticator($jwtEncoder);
        $isSupported = $authenticator->supports(new Request());
        $this->assertTrue($isSupported);

        $isSupported = $authenticator->supports(new Request([], [], [], [], [], ['REQUEST_URI' => '/api/v1/tokens']));
        $this->assertTrue($isSupported);
    }
}
