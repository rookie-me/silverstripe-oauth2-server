<?php

namespace AdvancedLearning\Oauth2Server\Models;

use AdvancedLearning\Oauth2Server\Entities\ClientEntity;
use function base64_encode;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\ORM\ArrayLib;
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
 * @method Image Logo()
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
     * @config
     * @var array
     */
    private static $available_grants = [
        "authorization_code",
        "client_credentials",
        "password",
        "refresh_token"
    ];

    private static $casting = [
        'Grants'        =>  'MultiEnum'
    ];
    
    /**
     * Remove ambiguity on Grants field by creating a checkbox
     * @return \SilverStripe\Forms\FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        // password,client_credentials,authorization,implicit,code,authorization_code
        $fields->replaceField('Grants', CheckboxSetField::create('Grants', 'Grants', ArrayLib::valuekey($this->config()->get('available_grants'))));
        return $fields;
    }

    /**
     * Checks whether this ClientEntity has the given grant type.
     *
     * @param string $grantType The grant type to check.
     *
     * @return boolean
     */
    public function hasGrantType($grantType)
    {
        $grants = json_decode($this->obj("Grants")->getValue());

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
