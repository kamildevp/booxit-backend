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
        private string $refreshTokenValue
    ) {
        parent::__construct($user, $firewallName, $roles);
    }

    public function getRefreshTokenValue(): string
    {
        return $this->refreshTokenValue;
    }

    /**
     * {@inheritdoc}
     */
    public function __serialize(): array
    {
        return [$this->refreshTokenValue, parent::__serialize()];
    }

    /**
     * {@inheritdoc}
     */
    public function __unserialize(array $data): void
    {
        [$this->refreshTokenValue, $parentData] = $data;
        parent::__unserialize($parentData);
    }
}
