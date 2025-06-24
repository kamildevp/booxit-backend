<?php

namespace App\Model;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

class PostAuthRefreshToken extends PostAuthenticationToken
{
    public function __construct(
        UserInterface $user,
        string $firewallName,
        array $roles,
        private string $refreshTokenValue,
        private int $refreshTokenId
    ) {
        parent::__construct($user, $firewallName, $roles);
    }

    public function getRefreshTokenValue(): string
    {
        return $this->refreshTokenValue;
    }

    public function getRefreshTokenId(): int
    {
        return $this->refreshTokenId;
    }

    /**
     * {@inheritdoc}
     */
    public function __serialize(): array
    {
        return [$this->refreshTokenValue, $this->refreshTokenId, parent::__serialize()];
    }

    /**
     * {@inheritdoc}
     */
    public function __unserialize(array $data): void
    {
        [$this->refreshTokenValue, $this->refreshTokenId, $parentData] = $data;
        parent::__unserialize($parentData);
    }
}
