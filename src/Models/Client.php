<?php

namespace AdvancedLearning\Oauth2Server\Models;

use AdvancedLearning\Oauth2Server\Entities\ClientEntity;
use function base64_encode;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use SilverStripe\Assets\Image;
use SilverStripe\ORM\DataObject;

/**
 * Stores ClientEntity information.
 *
 * @package AdvancedLearning\Oauth2Server\Models
 *
 * @property string $Name           Name of Client
 * @property string $Grants         Grant type; could be authorization_code,
 * @property string $Secret         Secret hash to confirm identity; should have some level of entropy
 * @property string $Identifier     Internal identifier; unique to each identity
 * @property string $RedirectUri    Default redirect URI for Client (if one is not provided)
 */
class Client extends DataObject
{
    private static $table_name = 'OauthClient';

    private static $singular_name = 'OAuth Client';

    private static $plural_name = 'OAuth Clients';

    private static $db = [
        'Name'          =>  'Varchar(100)',
        'Grants'        =>  'Varchar(255)',
        'Secret'        =>  'Varchar(32)',
        'Identifier'    =>  'Varchar(32)',
        'RedirectUri'   =>  'Varchar(255)'
    ];

    private static $has_one = [
        'Logo'          =>  Image::class
    ];

    private static $summary_fields = [
        'Name'
    ];

    /**
     * Checks whether this ClientEntity has the given grant type.
     *
     * @param string $grantType The grant type to check.
     *
     * @return boolean
     */
    public function hasGrantType($grantType)
    {
        $grants = explode(',', $this->Grants);
        $grants = array_map("trim", $grants);

        return !empty($grants) && in_array($grantType, $grants);
    }

    /**
     * On before write. Generate a secret if we don't have one.
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (empty($this->Secret)) {
            $this->Secret = $this->generateSecret();
        }

        if (empty($this->Identifier)) {
            $this->Identifier = $this->generateSecret();
        }
    }

    /**
     * Generate a random secret.
     *
     * @return string
     */
    protected function generateSecret()
    {
        return base64_encode(random_bytes(32));
    }

    /**
     * @return ClientEntity
     */
    public function getEntity($redirectURI = false){
        $redirectURI = $redirectURI?:$this->RedirectUri;
        return new ClientEntity($this->Identifier, $this->Name, $redirectURI);
    }

    /**
     * @return \SilverStripe\ORM\ValidationResult
     */
    public function validate()
    {
        $valid = parent::validate();

        if(Client::get()->filter(["Identifier"=>$this->Identifier, "ID:not"=>$this->ID])->exists()){
            $valid->addError("Identifier must be unique, please try again.");
        }

        return $valid;
    }
}
