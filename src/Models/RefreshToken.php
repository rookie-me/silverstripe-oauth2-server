<?php

namespace AdvancedLearning\Oauth2Server\Models;

use AdvancedLearning\Oauth2Server\Entities\RefreshTokenEntity;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;

/**
 * Class RefreshToken
 * @package AdvancedLearning\Oauth2Server\Entities
 */
class RefreshToken extends AccessToken
{
    private static $table_name = 'OauthRefreshToken';

    private static $singular_name = 'OAuth Refresh Token';

    private static $plural_name = 'OAuth Refresh Tokens';

    private static $has_one = [
        "AccessToken"   =>  AccessToken::class
    ];

    /**
     * @return RefreshTokenEntity
     */
    public function getEntity(){
        $entity = new RefreshTokenEntity($this->Identifier, $this->ScopeEntities());
        $entity->setClient($this->Client()->getEntity());
        return $entity;
    }
}
