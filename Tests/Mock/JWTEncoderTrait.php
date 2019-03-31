<?php

namespace App\Tests\Mock;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\LcobucciJWTEncoder;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\LcobucciJWSProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\CreatedJWS;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\LoadedJWS;

trait JWTEncoderTrait
{
    public $token = 'my-secret-token';

    protected function mockJWTEncoder()
    {
        $createdJws = new CreatedJWS($this->token, true);
        $loadedJws = new LoadedJWS([], true);

        $jwtProvider = $this->createMock(LcobucciJWSProvider::class);
        $jwtProvider->expects($this->any())
            ->method('create')
            ->willReturn($createdJws);
        $jwtProvider->expects($this->any())
            ->method('load')
            ->willReturn($loadedJws);

        $jwtEncoder = new LcobucciJWTEncoder($jwtProvider);

        return $jwtEncoder;
    }
}
