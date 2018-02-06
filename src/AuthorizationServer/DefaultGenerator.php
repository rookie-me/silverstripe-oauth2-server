<?php

namespace AdvancedLearning\Oauth2Server\AuthorizationServer;

use AdvancedLearning\Oauth2Server\Repositories\AccessTokenRepository;
use AdvancedLearning\Oauth2Server\Repositories\AuthCodeRepository;
use AdvancedLearning\Oauth2Server\Repositories\ClientRepository;
use AdvancedLearning\Oauth2Server\Repositories\RefreshTokenRepository;
use AdvancedLearning\Oauth2Server\Repositories\ScopeRepository;
use AdvancedLearning\Oauth2Server\Repositories\UserRepository;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use SilverStripe\Control\Director;
use SilverStripe\Core\Environment;

class DefaultGenerator implements Generator
{
    /**
     * @inheritdoc
     */
    public function getServer(): AuthorizationServer
    {
        // Init our repositories
        $authCodeRepository = new AuthCodeRepository();
        $clientRepository = new ClientRepository();
        $scopeRepository = new ScopeRepository();
        $accessTokenRepository = new AccessTokenRepository();
        $userRepository = new UserRepository();
        $refreshTokenRepository = new RefreshTokenRepository();

        // Path to public and private keys
        $privateKey = Environment::getEnv('OAUTH_PRIVATE_KEY_PATH');
        // inject base bath if necessary
        $privateKey = str_replace('{BASE_DIR}', Director::baseFolder(), $privateKey);

        $encryptionKey = Environment::getEnv('OAUTH_ENCRYPTION_KEY');

        // Setup the authorization server
        $server = new AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            $privateKey,
            $encryptionKey
        );

        // Create the authentication code grant
        $authCodeGrant = new AuthCodeGrant(
            $authCodeRepository,
            $refreshTokenRepository,
            new \DateInterval('PT1H')
        );

        // Enable the authentication code grant on the server
        $server->enableGrantType(
            $authCodeGrant,
            new \DateInterval('PT1H') // authentication codes will expire after 1 hour
        );
        $authCodeGrant->setRefreshTokenTTL(new \DateInterval('P1M')); // refresh tokens will expire after 1 month

        // Create the refresh token grant
        $refreshTokenGrant = new RefreshTokenGrant( $refreshTokenRepository);
        $refreshTokenGrant->setRefreshTokenTTL(new \DateInterval('P1M')); // refresh tokens will expire after 1 month

        // Enable the refresh token grant on the server (necessary for auth code grant)
        $server->enableGrantType(
            $refreshTokenGrant,
            new \DateInterval('PT1H') // refresh token will expire after 1 hour
        );

        // Enable the client credentials grant on the server
        $server->enableGrantType(
            new ClientCredentialsGrant(),
            new \DateInterval('PT1H') // access tokens will expire after 1 hour
        );

        // Enable password grant
        $server->enableGrantType(
            new PasswordGrant($userRepository, $refreshTokenRepository),
            new \DateInterval('PT1H')
        );

        return $server;
    }
}