<?php
/**
 * @package App\Controller
 * User: jdevergnies
 * Date: 2019-03-29
 * Time: 20:22
 */

namespace App\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\ControllerTrait;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Nelmio\ApiDocBundle\Annotation as Nelmio;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * @Security("is_anonymous() or is_authenticated()")
 */
class TokensController extends AbstractController
{
    use ControllerTrait;

    const DEFAULT_JWT_TTL = 7200;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var JWTEncoderInterface
     */
    private $jwtEncoder;

    /** @var array $entitiesClass */
    protected $entitiesClass;

    /**
     * TokensController constructor.
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param JWTEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder, JWTEncoderInterface $encoder, $entitiesClass)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->jwtEncoder = $encoder;
        $this->entitiesClass = $entitiesClass;
    }


    /**
     * @Rest\Route("/tokens")
     * @Rest\View(statusCode=201)
     * @SWG\Tag(name="authentication")
     * @SWG\Post(description="The token is mandatory in almost all the other endpoints. It requires a user and a password.")
     * @Nelmio\Security(name="Basic")
     * @SWG\Response(
     *     response=201,
     *     description="List of categories",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items({
     *             @SWG\Property(property="token", type="string"),
     *             @SWG\Property(property="user", type="array",
     *                 @SWG\Items({
     *                     @SWG\Property(property="id", type="integer"),
     *                     @SWG\Property(property="username", type="string")
     *                 })
     *             ),
     *             @SWG\Property(property="expiresIn", type="integer")
     *         })
     *     )
     * )
     */
    public function postTokenAction(Request $request)
    {
        $user = $this->getDoctrine()
            ->getRepository($this->entitiesClass['user'])
            ->findOneBy(['username' => $request->getUser()]);

        if (!$user || !$isPasswordValid = $this->passwordEncoder->isPasswordValid($user, $request->getPassword())) {
            throw new BadCredentialsException();
        }

        $timeToLive = (getenv('JWT_TTL')) ? getenv('JWT_TTL') : self::DEFAULT_JWT_TTL;

        $token = $this->jwtEncoder->encode([
            'username' => $user->getUsername(),
            'exp' => time() + $timeToLive
        ]);

        return [
            'token' => $token,
            'user' => ['id' => $user->getId(), 'username' => $user->getUsername()],
            'expiresIn' => $timeToLive
        ];
    }
}
