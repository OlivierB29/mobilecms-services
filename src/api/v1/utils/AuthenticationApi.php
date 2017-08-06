<?php

require_once 'RestApi.php';
require_once 'UserService.php';
/*
 * Login and creates users
 * /authapi/v1/auth
 * /authapi/v1/register
 */
class AuthenticationApi extends RestApi
{
    public function __construct($conf)
    {
        parent::__construct($conf);
    }

    /**
     * base API path /authapi/v1/authenticate.
     */
    protected function authenticate() : Response
    {
        $response = $this->getDefaultResponse();

        try {
            //throw error if wrong configuration, such as empty directory
            $this->checkConfiguration();

            $service = new UserService($this->conf->{'privatedir'}.'/users');

            // Preflight requests are send by Angular
            if ($this->method === 'OPTIONS') {
                // eg : /authapi/v1/auth
                $response = $service->preflight();
            }

            if ($this->method === 'POST') {
                if (!array_key_exists('requestbody', $this->request)) {
                    throw new Exception('no login request');
                }
                // login and get token
                    // eg : requestbody={ "user": "test@example.com", "password":"Sample#123456"}
                $logindata = json_decode($this->request['requestbody']);

                //TODO : user contains either email of name
                if (!isset($logindata)) {
                    throw new Exception('no login data');
                }
                $response = $service->getToken($logindata->{'user'}, $logindata->{'password'});
                unset($logindata);
                // free variables before response
            }
        } catch (Exception $e) {
            $response->setCode(401);
            $response->setMessage($e->getMessage());
            $response->setResult($this->errorToJson($e->getMessage()));
        } finally {
            return $response;
        }
    }

    /**
     * base API path /authapi/v1/authenticate.
     */
    protected function changepassword() : Response
    {
        $response = $this->getDefaultResponse();

        try {
            //throw error if wrong configuration, such as empty directory
            $this->checkConfiguration();

            $service = new UserService($this->conf->{'privatedir'}.'/users');

            // Preflight requests are send by Angular
            if ($this->method === 'OPTIONS') {
                // eg : /authapi/v1/auth
                $response->setResult($service->preflight());
            }

            if ($this->method === 'POST') {

                // login and get token
                    // eg : requestbody={ "user": "test@example.com", "password":"Sample#123456"}
                    $logindata = json_decode($this->request['requestbody']);

                //TODO : user contains either email of name

                // free variables before response
                $response = $service->changePassword($logindata->{'email'}, $logindata->{'password'}, $logindata->{'newpassword'});

                unset($logindata);
            }
        } catch (Exception $e) {
            $response->setCode(500);
            $response->setMessage($e->getMessage());
            $response->setResult($this->errorToJson($e->getMessage()));
        } finally {
            return $response;
        }
    }

    /**
     * /authapi/v1/register.
     */
    protected function register() : Response
    {
        $response = $this->getDefaultResponse();

        try {
            //throw error if wrong configuration, such as empty directory
            $this->checkConfiguration();
            $service = new UserService($this->conf->{'privatedir'}.'/users');

            // Preflight requests are send by Angular
            if ($this->method === 'OPTIONS') {
                $response->setResult($service->preflight());
            }

            // register and create a user
            if ($this->method === 'POST') {
                $user = json_decode($this->request['requestbody']);
                //returns a empty string if success, a string with the message otherwise
                $createresult = $service->createUserWithSecret($user->{'name'}, $user->{'email'}, $user->{'password'}, $user->{'secretQuestion'}, $user->{'secretResponse'}, 'create');
                if ($createresult === null) {
                    $response->setMessage('');
                    $response->setCode(200);
                    $response->setResult('{}');
                } else {
                    $response->setMessage('Bad user parameters');
                    $response->setCode(400);
                    $response->setResult($this->errorToJson('Bad user parameters'));
                }
            }
        } catch (Exception $e) {
            $response->setCode(500);
            $response->setMessage($e->getMessage());
            $response->setResult($this->errorToJson($e->getMessage()));
        } finally {
            return $response;
        }
    }

    /**
     * check if directory is defined.
     */
    private function checkConfiguration()
    {
        if (!isset($this->conf->{'privatedir'})) {
            throw new Exception('Empty publicdir');
        }
    }

    /**
     * http://stackoverflow.com/questions/25727306/request-header-field-access-control-allow-headers-is-not-allowed-by-access-contr.
     */
    public function preflight(): Response
    {
        $response = new Response();
        $response->setCode(200);
        $response->setResult('{}');

        header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE,OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

        return $response;
    }
}
