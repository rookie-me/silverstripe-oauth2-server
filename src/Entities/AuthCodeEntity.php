<?php

namespace AdvancedLearning\Oauth2Server\Entities;

use AdvancedLearning\Oauth2Server\Models\Client;
use function is_array;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AuthCodeTrait;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Security\Member;

/**
 * Class AuthCodeEntity
 * @package AdvancedLearning\Oauth2Server\Entities
 * @property string     Code
 * @property string     Expiry
 * @property boolean    Revoked
 * @method ClientEntity Client()
 * @method Member       Member()
 * @method ManyManyList ScopeEntities()
 */
class AuthCodeEntity implements AuthCodeEntityInterface
{
    use TokenEntityTrait, EntityTrait, AuthCodeTrait;

    /**
     * Create a new client instance.
     *
     * @param string $identifier  The identifier for the client.
     * @param array  $scopes      The scopes to assign the user.
     * @param string $redirectUri Redirect Uri.
     */
    public function __construct(string $identifier = '', array $scopes = [], ?string $redirectUri = null){

        $this->setIdentifier($identifier);

        if(is_array($scopes) && !empty($scopes)){
            foreach ($scopes as $scope) {
                $this->addScope($scope);
            }
        }

        if($redirectUri) {
            $this->setRedirectUri(explode(',', $redirectUri));
        }
    }

    /**
     * @return Client|null
     */
    public function getClientObject(){
        return Client::get()->find('Identifier', $this->client->getIdentifier());
    }

    /**
     * @return string|string[]
     */
    public function getRedirectUri(){
        $explicit = $this->redirectUri;
        $inherit = $this->getClientObject()?$this->getClientObject()->RedirectUri:"";
        return $explicit?:$inherit;
    }
}
