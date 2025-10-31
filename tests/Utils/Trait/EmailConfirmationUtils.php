<?php

declare(strict_types=1);

namespace App\Tests\Utils\Trait;

use App\Entity\EmailConfirmation;
use App\Enum\EmailConfirmation\EmailConfirmationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\UriSigner;

trait EmailConfirmationUtils
{
    protected function prepareEmailConfirmationVerifyParams(EmailConfirmationType $type): array
    {
        $emailConfirmationRepository = $this->container->get(EntityManagerInterface::class)->getRepository(EmailConfirmation::class);
        $emailConfirmation = $emailConfirmationRepository->findOneBy(['type' => $type->value]);

        $encodedData = json_encode([$emailConfirmation->getId(), $emailConfirmation->getEmail()]);
        $token = base64_encode(hash_hmac('sha256', $encodedData, $this->secret, true));
        
        $verifyParams = [
            'expires' => $emailConfirmation->getExpiryDate()->getTimestamp(),
            'id' => $emailConfirmation->getId(),
            'token' => $token,
            'type' => $emailConfirmation->getType(),
        ];

        $signer = $this->container->get(UriSigner::class);
        $url = $_ENV['VERIFICATION_HANDLER_TEST'] . '?' . http_build_query($verifyParams);
        $signedUrl = $signer->sign($url);
        $query = [];
        parse_str(parse_url($signedUrl)['query'], $query);
        $signature = $query['_hash'];

        return [
            ...$verifyParams, 
            '_hash' => $signature,
        ];
    }
}