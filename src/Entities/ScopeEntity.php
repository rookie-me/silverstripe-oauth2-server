<?php

namespace AdvancedLearning\Oauth2Server\Entities;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class ScopeEntity implements ScopeEntityInterface
{
    use EntityTrait;

    /**
     * ScopeEntity constructor.
     *
     * @param string $identifier The identifier of the scope.
     */
    public function __construct(string $identifier)
    {
        $this->setIdentifier($identifier);
    }

    /**
     * Get the scope in a format suitable for json.
     *
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->getIdentifier();
    }
}
