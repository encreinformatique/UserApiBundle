<?php

namespace tests\App\Controller\App;

use App\Tests\Mock\Entity\User;
use App\Tests\Mock\JWTEncoderTrait;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use EncreInformatique\UserApiBundle\Controller\TokensController;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class TokensControllerTest extends TestCase
{
    use JWTEncoderTrait;
    
    const USER_PASSWORD = 'abcd';

    /**
     * @var UserPasswordEncoder $encoder
     */
    private $encoder;

    /**
     * @test
     * @group Controller
     */
    public function exceptionBecauseNoUser()
    {
        $this->expectException(BadCredentialsException::class);

        $mockEntityRepository = $this->createMock(EntityRepository::class);
        $controller = $this->getController($mockEntityRepository);

        $controller->postTokenAction(new Request());
    }

    /**
     * @test
     * @group Controller
     */
    public function invalidPassword()
    {
        $this->expectException(BadCredentialsException::class);

        $user = new User('testuser', self::USER_PASSWORD, ['ROLE_USER']);
        $user->setPassword($this->encoder->encodePassword($user, self::USER_PASSWORD));

        $mockEntityRepository = $this->createMock(EntityRepository::class);
        $mockEntityRepository->method('findOneBy')
            ->willReturn($user);

        $controller = $this->getController($mockEntityRepository);

        $requestStack = new Request([], [], [], [], [], ['PHP_AUTH_USER' => 'testuser', 'PHP_AUTH_PW' => 'fgh']);
        $controller->postTokenAction($requestStack);
    }

    /**
     * @test
     * @group Controller
     */
    public function correctUser()
    {
        $user = new User('testuser', self::USER_PASSWORD, ['ROLE_USER']);
        $user->setPassword($this->encoder->encodePassword($user, self::USER_PASSWORD));

        $mockEntityRepository = $this->createMock(EntityRepository::class);
        $mockEntityRepository->method('findOneBy')
            ->willReturn($user);

        $controller = $this->getController($mockEntityRepository);

        $requestStack = new Request([], [], [], [], [], ['PHP_AUTH_USER' => 'testuser', 'PHP_AUTH_PW' => self::USER_PASSWORD]);
        $response = $controller->postTokenAction($requestStack);

        $this->assertTrue(is_array($response));

        $this->assertEquals($this->token, $response['token']);
        $this->assertEquals($user->getUsername(), $response['user']['username']);
        $this->assertEquals(TokensController::DEFAULT_JWT_TTL, $response['expiresIn']);
    }

    /**
     * @param $mockEntityRepository
     * @return TokensController
     */
    protected function getController($mockEntityRepository): TokensController
    {
        $mockDoctrine = $this->createMock(Registry::class);
        $mockDoctrine->method('getRepository')
            ->with(User::class)
            ->willReturn($mockEntityRepository);

        $mockContainer = $this->createMock(ContainerInterface::class);
        $mockContainer->method('has')
            ->with('doctrine')
            ->willReturn(true);
        $mockContainer->method('get')
            ->with('doctrine')
            ->willReturn($mockDoctrine);

        $controller = new TokensController($this->encoder, $this->mockJWTEncoder(), ['user' => User::class]);
        $controller->setContainer($mockContainer);
        return $controller;
    }

    public function setUp()
    {
        parent::setUp();

        $this->encoder = new UserPasswordEncoder(
            new EncoderFactory(array('App\Tests\Mock\Entity\User' => array(
                'class' => 'Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder',
                'arguments' => array('sha512', true, 5),
            )))
//            new EncoderFactory([new BCryptPasswordEncoder(4)])
        );
    }
}
