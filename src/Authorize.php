<?php

namespace Chadicus\Slim\OAuth2\Routes;

use Chadicus\Slim\OAuth2\Http\MessageBridge;
use OAuth2;
use Slim\Slim;

class Authorize
{
    const ROUTE = '/authorize';

    private $slim;
    private $server;

    public function __construct(Slim $slim, OAuth2\Server $server)
    {
        $this->slim = $slim;
        $this->server = $server;
    }

    public function __invoke()
    {
        $request = MessageBridge::newOAuth2Request($this->slim->request());
        $response = new OAuth2\Response();
        $isValid = $this->server->validateAuthorizeRequest($request, $response);
        if (!$isValid) {
            MessageBridge::mapResponse($response, $this->slim->response());
            return;
        }

        $authorized = $this->slim->request()->params('authorized');
        if (empty($authorized)) {
            //@TODO send to authorize landing page
            return;
        }

        //@TODO implement user_id
        $this->server->handleAuthorizeRequest($request, $response, $authorized === 'yes');

        MessageBridge::mapResponse($response, $this->slim->response());
    }

    /**
     * Register this route with the given Slim application and OAuth2 server
     *
     * @param Slim          $slim   The slim framework application instance.
     * @param OAuth2\Server $server The oauth2 server imstance.
     *
     * @return void
     */
    public static function register(Slim $slim, OAuth2\Server $server)
    {
        $slim->map(self::ROUTE, new self($slim, $server))->via('GET', 'POST')->name('authorize');
    }
}