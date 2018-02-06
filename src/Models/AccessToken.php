<?php

namespace AdvancedLearning\Oauth2Server\Models;

use AdvancedLearning\Oauth2Server\Entities\AccessTokenEntity;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

/**
 * Class AccessTokenEntity
 *
 * @package AdvancedLearning\Oauth2Server\Models
 *
 * @property int    $ID
 * @property string $Identifier     this is randomly generated unique identifier (of 80+ characters in length) for the access token.
 * @property string $Expiry         the expiry date and time of the access token.
 * @property string $MemberID       the user identifier represented by the access token.
 * @property string $ClientID       the client identifier represented by the access token.
 * @property bool   $Revoked
 */
class AccessToken extends DataObject
{
    private static $table_name = 'OauthAccessToken';

    private static $singular_name = 'OAuth Access Token';

    private static $plural_name = 'OAuth Access Tokens';

    private static $db = [
        'Identifier'        => 'Varchar(255)',
        'Expiry'            => 'Datetime',
        'Revoked'           => 'Boolean',
    ];

    private static $has_one = [
        'Client'            => Client::class,
        'Member'            => Member::class,
    ];

    private static $many_many = [
        'Scopes'            => Scope::class,
    ];

    private static $summary_fields = [
        'ClassName',
        'Client.Name',
        'Member.Name',
        'Expiry.Ago',
        'Revoked'
    ];

    /**
     * @return bool
     */
    public function Expired(){
        return (bool) $this->obj("Expiry")->InPast();
    }

    /**
     * @param \DateTime $expiry
     * @return $this
     */
    public function setExpiryDateTime(\DateTime $expiry) {
        $this->Expiry = DBField::create_field('Datetime', $expiry->getTimestamp());
        return $this;
    }

    /**
     * @return ScopeEntityInterface[]
     */
    public function ScopeEntities() {
        $return = [];
        foreach($this->Scopes() as $scope){
            $return[$scope->Identifier] = $scope->getEntity();
        }
        return $return;
    }

    /**
     * @return AccessTokenEntity
     */
    public function getEntity(){
        $entity = new AccessTokenEntity($this->Identifier, $this->ScopeEntities());
        $entity->setClient($this->Client()->getEntity());
        return $entity;
    }
}
