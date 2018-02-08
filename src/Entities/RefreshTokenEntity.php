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
     * @param null|string $identifier                            The identifier of the refreshtoken.
     * @param DateTime|null $expiryDateTime                      Set the date time when the token expires.
     * @param AccessTokenEntityInterface|null $accessToken  Set the access token that the refresh token was associated with.
     */
    public function __construct(?string $identifier = '', ?DateTime $expiryDateTime = null, ?AccessTokenEntityInterface $accessToken = null)
    {
        $this->setIdentifier($identifier);
        if($expiryDateTime) {
            $this->setExpiryDateTime($expiryDateTime);
        }
        if($accessToken) {
            $this->setAccessToken($accessToken);
        }
    }
}
