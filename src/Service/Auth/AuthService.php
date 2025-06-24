<?php

namespace App\Service\Auth;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Exceptions\InvalidObjectException;
use App\Exceptions\RefreshTokenCompromisedException;
use App\Exceptions\TokenRefreshFailedException;
use App\Model\RefreshTokenPayload;
use App\Repository\RefreshTokenRepository;
use DateInterval;
use DateTime;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\Token\JWTPostAuthenticationToken;
use Symfony\Bundle\SecurityBundle\Security;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private Security $security, 
        private RefreshTokenRepository $refreshTokenRepository, 
        private JWTEncoderInterface $jwtEncoder,
        private int $refreshTokenTTL
    )
    {

    }

    public function createUserRefreshToken(User $user): RefreshToken
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setAppUser($user);
        $this->refreshTokenRepository->save($refreshToken, true);

        $this->regenerateRefreshToken($refreshToken);
        $this->refreshTokenRepository->save($refreshToken, true);
        
        return $refreshToken;
    }

    public function refreshUserToken(string $refreshTokenValue): RefreshToken
    {
        try{
            $refreshToken = $this->resolveRefreshToken($refreshTokenValue);
            $this->regenerateRefreshToken($refreshToken);
            $this->refreshTokenRepository->save($refreshToken, true);
        }
        catch(RefreshTokenCompromisedException $e){
            $this->refreshTokenRepository->removeAllUserRefreshTokens($e->getUser());
            throw new TokenRefreshFailedException();
        }

        return $refreshToken;
    }

    private function generateRefreshTokenValue(User $user, int $refreshTokenId, DateTime $expiryDate): string
    {
        return $this->jwtEncoder->encode([
            'id' => $user->getId(),
            'roles' => $user->getRoles(),
            'refresh_token_id' => $refreshTokenId,
            'exp' => $expiryDate->getTimestamp(),
        ]);
    }

    private function generateRefreshTokenExpiryDate(int $ttl): DateTime
    {
        $interval = new DateInterval('PT' . $ttl . 'S');
        $expiryDate = new DateTime();
        $expiryDate->add($interval);
        
        return $expiryDate;
    }

    private function regenerateRefreshToken(RefreshToken $refreshToken): void
    {
        $expiryDate = $this->generateRefreshTokenExpiryDate($this->refreshTokenTTL);
        $refreshToken->setExpiresAt($expiryDate);
        $value = $this->generateRefreshTokenValue($refreshToken->getAppUser(), $refreshToken->getId(), $expiryDate);
        $refreshToken->setValue($value);
    }

    private function resolveRefreshToken(string $refreshTokenValue): RefreshToken
    {
        $tokenPayload = $this->parseRefreshToken($refreshTokenValue);
        $refreshToken = $this->refreshTokenRepository->find($tokenPayload->getRefreshTokenId());
        if($refreshToken == null){
            throw new TokenRefreshFailedException();
        }

        $refreshTokenUser = $refreshToken->getAppUser();
        if($refreshTokenUser->getId() != $tokenPayload->getUserId() || $refreshToken->getValue() != $refreshTokenValue){
            throw new RefreshTokenCompromisedException($refreshTokenUser);
        }

        return $refreshToken;
    }


    private function parseRefreshToken(string $refreshToken): RefreshTokenPayload
    {
        try{
            $tokenPayload = $this->jwtEncoder->decode($refreshToken);
        }
        catch(JWTDecodeFailureException $e){
            throw new TokenRefreshFailedException();
        }
        
        if($tokenPayload === false || !isset($tokenPayload['id']) || !isset($tokenPayload['refresh_token_id'])){
            throw new TokenRefreshFailedException();
        }

        return new RefreshTokenPayload((int)$tokenPayload['id'], (int)$tokenPayload['refresh_token_id']);
    }

    public function getRefreshTokenUsedByCurrentUser(): ?RefreshToken
    {
        $token = $this->security->getToken();
        if(!$token instanceof JWTPostAuthenticationToken){
            $class = JWTPostAuthenticationToken::class;
            throw new InvalidObjectException("Token must be instance of $class");
        }

        $payload = $this->jwtEncoder->decode($token->getCredentials());
        if(!array_key_exists('refresh_token_id', $payload) || !is_int($payload['refresh_token_id'])){
            return null;
        }

        return $this->refreshTokenRepository->find((int)$payload['refresh_token_id']);
    }
}