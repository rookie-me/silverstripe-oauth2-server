<?php

namespace AdvancedLearning\Oauth2Server\Repositories;

use AdvancedLearning\Oauth2Server\Entities\ClientEntity;
use AdvancedLearning\Oauth2Server\Entities\ScopeEntity;
use AdvancedLearning\Oauth2Server\Models\Scope;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class ScopeRepository implements ScopeRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getScopeEntityByIdentifier($identifier)
    {
        if ($scope = Scope::get()->filter(['Identifier' => $identifier])->first()) {
            return $scope->getEntity();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ) {
        $client = $clientEntity instanceof ClientEntity ? $client->getClientObject() : false;
        if($client){
            $client->extend("updateScopes", $scopes, $grantType, $userIdentifier);
        }
        return $scopes;
    }
}
