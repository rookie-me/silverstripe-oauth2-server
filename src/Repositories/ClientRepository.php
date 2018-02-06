<?php

namespace AdvancedLearning\Oauth2Server\Repositories;

use AdvancedLearning\Oauth2Server\Entities\ClientEntity;
use AdvancedLearning\Oauth2Server\Models\Client;
use function hash_equals;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true)
    {
        /** @var $client Client */
        $client = Client::get()->filter(['Identifier' => $clientIdentifier])->first();

        if ($mustValidateSecret && $client && !hash_equals($client->Secret, $clientSecret)) {
            $client = null;
        }

        return $client && $client->hasGrantType($grantType) ? $client->getEntity() : null;
    }
}
