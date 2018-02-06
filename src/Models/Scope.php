<?php

namespace AdvancedLearning\Oauth2Server\Models;

use AdvancedLearning\Oauth2Server\Entities\ScopeEntity;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use SilverStripe\ORM\DataObject;

/**
 * Class Scope
 *
 * @package AdvancedLearning\Oauth2Server\Models
 *
 * @property string $Name           Nice name for App-side identification
 * @property string $Identifier     Identifier for Client-side identification
 * @property string $Description    Short description of what the Scope is, useful for tooltips
 */
class Scope extends DataObject
{
    private static $table_name = 'OauthScope';

    private static $singular_name = 'OAuth Scope';

    private static $plural_name = 'OAuth Scopes';

    private static $db = [
        'Name'          => 'Varchar(100)',
        'Identifier'    => 'Varchar(100)',
        'Description'   => 'Varchar(255)'
    ];

    private static $belongs_many_many = [
        'AccessTokens'  =>  AccessToken::class
    ];

    private static $summary_fields = [
        'Name',
        'Identifier',
        'Description',
        'AccessTokens.Count'    =>  'Used by'
    ];

    private static $field_labels = [
        'Name'          =>  'Nice Name',
        'Identifier'    =>  'OAuth Identifier (used by OAuth Clients)',
    ];

    /**
     * @return ScopeEntity
     */
    public function getEntity(){
        return new ScopeEntity($this->Identifier);
    }

}
