<?php

namespace App\Business\WebAuthnImplementation;

use Cose\Algorithm\Manager;
use Cose\Algorithm\Signature\ECDSA\ES256;
use Cose\Algorithm\Signature\ECDSA\ES256K;
use Cose\Algorithm\Signature\ECDSA\ES384;
use Cose\Algorithm\Signature\ECDSA\ES512;
use Cose\Algorithm\Signature\EdDSA\Ed256;
use Cose\Algorithm\Signature\EdDSA\Ed512;
use Cose\Algorithm\Signature\RSA\PS256;
use Cose\Algorithm\Signature\RSA\PS384;
use Cose\Algorithm\Signature\RSA\PS512;
use Cose\Algorithm\Signature\RSA\RS256;
use Cose\Algorithm\Signature\RSA\RS384;
use Cose\Algorithm\Signature\RSA\RS512;
use Cose\Algorithms;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRpEntity;

class Server
{
    public static function getRpEntity()
    {
        return new PublicKeyCredentialRpEntity(
            tenant()->getOption('CLUB_NAME'),
            tenant('Domain'),
            //            'data:image/png;base64,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'
        );
    }

    public static function getPublicKeyParameterList(): array
    {
        return [
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_ES256),
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_ES256K),
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_ES384),
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_ES512),
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_RS256),
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_RS384),
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_RS512),
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_PS256),
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_PS384),
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_PS512),
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_ED256),
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_ED512),
        ];
    }

    public static function getAlgorithmManager(): Manager
    {
        return Manager::create()
            ->add(
                ES256::create(),
                ES256K::create(),
                ES384::create(),
                ES512::create(),

                RS256::create(),
                RS384::create(),
                RS512::create(),

                PS256::create(),
                PS384::create(),
                PS512::create(),

                Ed256::create(),
                Ed512::create(),
            );
    }
}
