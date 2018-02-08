<?php

namespace AdvancedLearning\Oauth2Server\Repositories;

use AdvancedLearning\Oauth2Server\Entities\AuthCodeEntity;
use AdvancedLearning\Oauth2Server\Models\AuthCode;
use AdvancedLearning\Oauth2Server\Models\Client;
use AdvancedLearning\Oauth2Server\Models\Scope;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use SilverStripe\Security\Member;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getNewAuthCode()
    {
        return new AuthCodeEntity();
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $newToken = AuthCode::create();

        $newToken->Identifier = $authCodeEntity->getIdentifier();
        $newToken->Expiry  = $authCodeEntity->getExpiryDateTime()->format('Y-m-d H:i');

        // @todo - Are both of these required?
        $member = Member::get()->byID($authCodeEntity->getUserIdentifier());
        if(!$member){
            throw new Exception("Couldn't find user by Identifier");
        }
        $newToken->MemberID = $member->ID;

        $client = Client::get()->find("Identifier", $authCodeEntity->getClient()->getIdentifier());
        if(!$client){
            throw new Exception("Couldn't find client by Identifier");
        }
        $newToken->ClientID = $client->ID;
        $newToken->write(); // need to write before creating manymany relationships

        foreach ($authCodeEntity->getScopes() as $scopeEntity) {
            if( $scope = Scope::get()->find("Identifier", $scopeEntity->getIdentifier()) )
                $newToken->Scopes()->add($scope);
        }

        $newToken->write();

        return $newToken;
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAuthCode($codeId)
    {
        // Some logic to revoke the auth code in a database
        if( $code = $this->findAuthCode($codeId) ) {
            $code->Revoked = true;
            $code->write();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthCodeRevoked($codeId)
    {
        $code = $this->findAuthCode($codeId);

        // return true if there is no matching code
        return empty($code) || $code->Revoked;
    }

    /**
     * @param string $codeId
     * @return AuthCodeEntity
     */
    public function findAuthCode($codeId)
    {
        return AuthCode::get()->filter(['ClassName'=>AuthCode::class, 'Identifier' => $codeId])->first();
    }

}
