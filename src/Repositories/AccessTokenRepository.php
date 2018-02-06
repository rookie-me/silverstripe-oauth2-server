<?php

namespace AdvancedLearning\Oauth2Server\Repositories;

use AdvancedLearning\Oauth2Server\Entities\AccessTokenEntity;
use AdvancedLearning\Oauth2Server\Models\AccessToken;
use AdvancedLearning\Oauth2Server\Models\Client;
use AdvancedLearning\Oauth2Server\Models\Scope;
use Exception;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        $newToken = AccessToken::create();

        $newToken->Identifier = $accessTokenEntity->getIdentifier();
        $newToken->Expiry  = $accessTokenEntity->getExpiryDateTime()->format('Y-m-d H:i');

        // @todo - Are both of these required?
        $member = Member::get()->byID($accessTokenEntity->getUserIdentifier());
        if(!$member){
            throw new Exception("Couldn't find user by Identifier");
        }
        $newToken->MemberID = $member->ID;

        $client = Client::get()->find("Identifier", $accessTokenEntity->getClient()->getIdentifier());
        if(!$client){
            throw new Exception("Couldn't find client by Identifier");
        }
        $newToken->ClientID = $client->ID;
        $newToken->write(); // need to write before creating manymany relationships

        foreach ($accessTokenEntity->getScopes() as $scopeEntity) {
            if( $scope = Scope::get()->find("Identifier", $scopeEntity->getIdentifier()) )
                $newToken->Scopes()->add($scope);
        }

        $newToken->write();

        return $newToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        $entity = new AccessTokenEntity($userIdentifier, $scopes);
        $entity->setClient($clientEntity);
        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAccessToken($tokenId)
    {
        if ($token = $this->findToken($tokenId)) {
            $token->Revoked = true;
            $token->write();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isAccessTokenRevoked($tokenId): bool
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
     * @return AccessToken|null
     */
    public function findToken(string $tokenId): ?AccessToken
    {
        return AccessToken::get()->filter(['ClassName'=>AccessToken::class, 'Identifier' => $tokenId])->first();
    }
}
