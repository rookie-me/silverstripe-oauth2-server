<?php

namespace AdvancedLearning\Oauth2Server\Entities;

use AdvancedLearning\Oauth2Server\Models\Client;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class ClientEntity implements ClientEntityInterface
{
    use ClientTrait, EntityTrait;

    /**
     * Create a new client instance.
     *
     * @param string $identifier  The identifier for the client.
     * @param string $name        The name of the client.
     * @param string $redirectUri Redirect Uri.
     */
    public function __construct(string $identifier, string $name, string $redirectUri)
    {
        $this->setIdentifier($identifier);
        $this->name = $name;
        $this->redirectUri = explode(',', $redirectUri);
    }

    /**
     * @return Client|null
     */
    public function getClientObject(){
        return Client::get()->find('Identifier', $this->getIdentifier());
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
