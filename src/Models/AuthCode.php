<?php

namespace AdvancedLearning\Oauth2Server\Models;

use AdvancedLearning\Oauth2Server\Entities\AuthCodeEntity;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;

/**
 * Class AuthCode
 * @package AdvancedLearning\Oauth2Server\Entities
 */
class AuthCode extends AccessToken
{
    private static $singular_name = 'OAuth Auth Code';

    private static $plural_name = 'OAuth Auth Codes';

    /**
     * @return AuthCodeEntity
     */
    public function getEntity(){
        $entity = new AuthCodeEntity($this->Identifier, $this->ScopeEntities());
        $entity->setClient($this->Client()->getEntity());
        return $entity;
    }
}
