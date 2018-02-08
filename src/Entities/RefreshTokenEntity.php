<?php

namespace AdvancedLearning\Oauth2Server\Entities;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;

class RefreshTokenEntity implements RefreshTokenEntityInterface
{
    use EntityTrait, RefreshTokenTrait;

    /**
     * RefreshTokenEntity constructor.
     * @param string $identifier                        The identifier of the refreshtoken.
     * @param DateTime $expiryDateTime                  Set the date time when the token expires.
     * @param AccessTokenEntityInterface $accessToken   Set the access token that the refresh token was associated with.
     */
    public function create(string $identifier, DateTime $expiryDateTime, AccessTokenEntityInterface $accessToken)
    {
        $this->setIdentifier($identifier);
        $this->setExpiryDateTime($expiryDateTime);
        $this->setAccessToken($accessToken);
    }
}
