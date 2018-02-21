<?php

namespace AdvancedLearning\Oauth2Server\Controllers;

use AdvancedLearning\Oauth2Server\AuthorizationServer\Generator;
use AdvancedLearning\Oauth2Server\Entities\UserEntity;
use Exception;
use GuzzleHttp\Psr7\Response;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Robbie\Psr7\HttpRequestAdapter;
use Robbie\Psr7\HttpResponseAdapter;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTP;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\Debug;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * Class OauthServerController
 * @package AdvancedLearning\Oauth2Server\Controllers
 * @todo add a 'revoke' endpoint
 */
class OauthServerController extends Controller
{
    /**
     * @var Generator
     */
    protected $serverGenerator;

    /**
     * @var HttpRequestAdapter
     */
    private $myRequestAdapter;

    /**
     * @var HttpResponseAdapter
     */
    private $myResponseAdapter;

    /**
     * @var HTTPRequest
     */
    protected $myRequest;

    /**
     * @var HTTPResponse
     */
    protected $myResponse;

    private static $allowed_actions = [
        'authorize',
        'access_token',
        'resource'
    ];

    private static $url_handlers = [
        'authorize' 		=> 'authorize',
        'access_token'		=> 'access_token',
        'resource'		    => 'resource',
    ];

    private static $url_segment = "oauth2";

    /**
     * AuthoriseController constructor. If no Authorization Service is passed a default one is created.
     *
     * @param Generator $serverGenerator
     */
    public function __construct(Generator $serverGenerator)
    {
        $this->serverGenerator = $serverGenerator;
        parent::__construct();
    }

    /**
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function handleRequest(HTTPRequest $request) {
        $parsedBody = ($request->getHeader('Content-Type') === 'application/json') ? json_decode($request->getBody(), true) : false;

        $this->myRequest = (new HttpRequestAdapter())->toPsr7($request);
        if($parsedBody){
            $this->myRequest->withParsedBody($parsedBody);
        }

        $this->myResponse = (new HttpResponseAdapter())->toPsr7($this->getResponse());
        return parent::handleRequest($request);
    }

    /**
     * Handles authorize request.
     *
     * @return HTTPResponse
     */
    public function authorize(): HTTPResponse
    {

        $authServer = $this->serverGenerator->getServer();

        try {
            // Validate the HTTP request and return an AuthorizationRequest object.
            $authRequest = $authServer->validateAuthorizationRequest($this->myRequest);

            // The auth request object can be serialized and saved into a user's session.
            $member = Security::getCurrentUser();
            if( !$member ) {
                // You will probably want to redirect the user at this point to a login endpoint.
                return $this->redirect(
                    Config::inst()->get(Security::class, 'login_url')
                    . "?BackURL=" . urlencode($_SERVER['REQUEST_URI'])
                );
            }

            // Once the user has logged in set the user on the AuthorizationRequest
            $authRequest->setUser(new UserEntity($member)); // an instance of UserEntityInterface

            // At this point you should redirect the user to an authorization page.
            // This form will ask the user to approve the client and the scopes requested.
            // This could be dependant on the client, the grant type or anything like that
            // This should also compare the required scopes to the previously allowed scopes to determine
            // if a confirmation is required or not

            // Once the user has approved or denied the client update the status
            // (true = approved, false = denied)
            $authRequest->setAuthorizationApproved(true);

            // Return the HTTP redirect response
            $this->myResponse = $authServer->completeAuthorizationRequest($authRequest, $this->myResponse);
        } catch (OAuthServerException $e) {
            $this->myResponse =  $e->generateHttpResponse(new Response());
        } catch (Exception $e) {
            $this->myResponse = $this->getErrorResponse($e->getMessage());
        }

        return $this->convertResponse($this->myResponse);
    }

    /**
     * Handles access token request.
     *
     * @return HTTPResponse
     */
    public function access_token(): HTTPResponse
    {

        $authServer = $this->serverGenerator->getServer();

        try {
            $this->myResponse = $authServer->respondToAccessTokenRequest($this->myRequest, $this->myResponse);
        } catch (OAuthServerException $e) {
            $this->myResponse =  $e->generateHttpResponse(new Response());
        } catch (Exception $e) {
            $this->myResponse = $this->getErrorResponse($e->getMessage());
        }

        return $this->convertResponse($this->myResponse);
    }

    /**
     * @param $message
     * @param int $responseCode
     * @return ResponseInterface
     */
    protected function getErrorResponse($message, $responseCode = 500)
    {
        Debug::endshow($message);
        $response = (new OAuthServerException($message, 100, 'server_error', $responseCode))
            ->generateHttpResponse(new Response());

        return $response;
    }

    /**
     * @param ResponseInterface $response
     * @return \SilverStripe\Control\HTTPRequest|HTTPResponse
     */
    protected function convertResponse(ResponseInterface $response)
    {
        return (new HttpResponseAdapter())->fromPsr7($response);
    }

    /**
     * @todo - based on the authorisation provided to this user (scopes), we should return a json_encoded array of data
     * @return string
     */
    public function resource()
    {
        $member = Member::currentUser();
        if(!$member){
            $return = [
                'error'     =>  'Member not found'
            ];
        }else{
            $return = [
                'ID'        => $member->ID,
                'FirstName' => $member->FirstName,
                'Surname'   => $member->Surname,
                'Email'     => $member->Email
            ];
        }
        return json_encode($return);
    }
}
