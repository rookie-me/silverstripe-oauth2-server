<?php

namespace AdvancedLearning\Oauth2Server\Repositories;

use AdvancedLearning\Oauth2Server\Entities\RefreshTokenEntity;
use AdvancedLearning\Oauth2Server\Models\AccessToken;
use AdvancedLearning\Oauth2Server\Models\RefreshToken;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $newToken = RefreshToken::create();

        $newToken->Identifier = $refreshTokenEntity->getIdentifier();
        $newToken->Expiry  = $refreshTokenEntity->getExpiryDateTime()->format('Y-m-d H:i');

        // this is ambiguous - could find an AccessToken or an AuthToken... not sure if that's a good thing
        $accessToken = AccessToken::get()->find("Identifier", $refreshTokenEntity->getAccessToken()->getIdentifier());
        if(!$accessToken){
            throw new Exception("Couldn't find access token by Identifier for refresh");
        }

        $newToken->MemberID = $accessToken->MemberID;
        $newToken->ClientID = $accessToken->ClientID;
        $newToken->write(); // need to write before creating manymany relationships

        $newToken->Scopes()->setByIDList($accessToken->Scopes()->column("ID"));
        $newToken->write();

        return $newToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewRefreshToken()
    {
        return new RefreshTokenEntity();
    }

    /**
     * {@inheritdoc}
     */
    public function revokeRefreshToken($tokenId)
    {
        if ($token = $this->findToken($tokenId)) {
            $token->Revoked = true;
            $token->write();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        $token = $this->findToken($tokenId);

        // return true if there is no matching token
        return empty($token) || $token->Revoked;
    }

    /**
     * Find the Token for passed id.
     *
     * @param string $tokenId The id of the token.
     *
     * @return RefreshToken|null
     */
    public function findToken(string $tokenId): ?RefreshToken
    {
        return RefreshToken::get()->filter(['Identifier' => $tokenId])->first();
    }
}
