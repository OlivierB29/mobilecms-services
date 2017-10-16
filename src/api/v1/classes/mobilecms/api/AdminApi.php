<?php namespace mobilecms\api;

// require_once 'SecureRestApi.php';
// require_once '\mobilecms\utils\ContentService.php';
// require_once '\mobilecms\utils\UserService.php';
// require_once 'JsonUtils.php';
/**
 * Administration API (users, ...).
 */
class AdminApi extends \mobilecms\utils\SecureRestApi
{
    const INDEX_JSON = '/index/index.json';

    const EMAIL = 'email';

    /**
     * Constructor.
     *
     * @param \stdClass $conf JSON configuration
     */
    public function __construct($conf)
    {
        parent::__construct($conf);
        // Default headers for RESTful API
        if ($this->enableHeaders) {
            header('Access-Control-Allow-Methods: *');
            header('Content-Type: application/json');
        }
        $this->role = 'admin';
    }


    /**
     * Base API path /api/v1/content.
     *
     * @return \mobilecms\utils\Response object
     */
    protected function content() : \mobilecms\utils\Response
    {
        $response = $this->getDefaultResponse();

        $this->checkConfiguration();
        $datatype = $this->getDataType();

        $service = new \mobilecms\utils\ContentService($this->getPrivateDirPath());

        // Preflight requests are send by Angular
        if ($this->method === 'OPTIONS') {
            // eg : /api/v1/content
            $response = $this->preflight();
        }

        if (!empty($datatype)) {
            $pathId = $this->getId();

            // eg : /api/v1/content/calendar
            if ($this->method === 'GET') {
                if (!empty($pathId)) {
                    // get the full data of a single record. $this->args contains the remaining path parameters
                    // eg : /api/v1/content/calendar/1/foo/bar --> ['1', 'foo', 'bar']
                    $tmpResponse = $service->getRecord($datatype, $pathId);
                    // basic user fields, without password
                    if ($tmpResponse->getCode() === 200) {
                        $response->setCode(200);
                        $response->setResult($this->getUserResponse($tmpResponse->getResult()));
                    }
                } else {
                    //get all records in index
                    $response = $service->getAllObjects($datatype);
                }
            } elseif ($this->method === 'POST') {
                $userService = new \mobilecms\utils\UserService($this->getPrivateDirPath() . '/users');

                if (!empty($pathId)) {
                    // save a record and update the index. eg : /api/v1/content/calendar
                    // step 1 : update Record

                    // update password if needed
                    $userParam = urldecode($this->getRequestBody());
                    $user = json_decode($userParam);
                    if (isset($user->{'newpassword'})) {
                        $response = $userService->changePasswordByAdmin($user->{'email'}, $user->{'newpassword'});
                    }

                    $putResponse = $service->update($datatype, self::EMAIL, $this->getUserResponse($userParam));

                    $myobjectJson = json_decode($putResponse->getResult());
                    unset($putResponse);

                    // step 2 : publish to index
                    $id = $myobjectJson->{self::EMAIL};
                    unset($myobjectJson);
                    $response = $service->publishById($datatype, self::EMAIL, $id);
                } else {
                    // get all properties of a user, unless $user->{'property'} will fail if the request is empty
                    $user = $this->getDefaultUser();
                    // get parameters from request
                    $requestuser = json_decode($this->getRequestBody());

                    JsonUtils::copy($requestuser, $user);

                    //returns a empty string if success, a string with the message otherwise

                    $createresult = $userService->createUserWithSecret(
                        $user->{'name'},
                        $user->{'email'},
                        $user->{'password'},
                        $user->{'secretQuestion'},
                        $user->{'secretResponse'},
                        'create'
                    );
                    if (empty($createresult)) {
                        $id = $user->{self::EMAIL};
                        $response = $service->publishById($datatype, self::EMAIL, $id);
                        unset($user);
                        $response->setResult('{}');
                        $response->setCode(200);
                    } else {
                        $response->setError(400, $createresult);
                    }
                }
            } elseif ($this->method === 'PUT') {
            } elseif ($this->method === 'DELETE') {
                if (!empty($pathId)) {
                    // delete a single record. $this->args contains the remaining path parameters
                    // eg : /api/v1/content/calendar/1/foo/bar --> ['1', 'foo', 'bar']
                    $response = $service->deleteRecord($datatype, $pathId);
                    if ($response->getCode() === 200) {
                        // rebuild index
                        $response = $service->rebuildIndex($datatype, self::EMAIL);
                    }
                }
                // delete a record and update the index. eg : /api/v1/content/calendar/1.json
            }
        } else {
            if ($this->method === 'GET') {
                //return the list of editable types. eg : /api/v1/content/
                $response->setResult($service->options('types.json'));
                $response->setCode(200);
            }
        }
        // set a timestamp response
        $tempResponse = json_decode($response->getResult());
        $tempResponse->{'timestamp'} = '' . time();
        $response->setResult(json_encode($tempResponse));

        return $response;
    }



    /**
     * Basic user fields, without password.
     *
     * @param userStr $userStr JSON user string
     *
     * @return \stdClass JSON user string
     */
    public function getUserResponse($userStr): string
    {
        $completeUserObj = json_decode($userStr);
        $responseUser = json_decode('{}');
        $responseUser->{'name'} = $completeUserObj->{'name'};
        $responseUser->{'email'} = $completeUserObj->{'email'};
        $responseUser->{'role'} = $completeUserObj->{'role'};

        return json_encode($responseUser);
    }



    /**
     * Preflight response.
     *
     * http://stackoverflow.com/questions/25727306/request-header-field-access-control-allow-headers-is-not-allowed-by-access-contr.
     *
     * @return \mobilecms\utils\Response object
     */
    public function preflight(): \mobilecms\utils\Response
    {
        $response = new \mobilecms\utils\Response();
        $response->setCode(200);
        $response->setResult('{}');

        header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE,OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

        return $response;
    }

    /**
     * Get or refresh index.
     *
     * @return \mobilecms\utils\Response object
     */
    protected function index() : \mobilecms\utils\Response
    {
        $userKey = 'email';
        $response = $this->getDefaultResponse();
        $datatype = $this->getDataType();

        $this->checkConfiguration();

        // Preflight requests are send by Angular
        if ($this->method === 'OPTIONS') {
            // eg : /api/v1/content
            $response = $this->preflight();
        } elseif (!empty($datatype)) {
            $service = new \mobilecms\utils\ContentService($this->getPrivateDirPath());

            // eg : /api/v1/content/calendar
            if ($this->method === 'GET') {
                if (!empty($pathId)) {
                    //TODO get single index value
                } else {
                    $response = $service->getAll($datatype . '/index/index.json');
                }
            } elseif ($this->method === 'POST') {
                $response = $service->rebuildIndex($datatype, $userKey);
            }
        }

        return $response;
    }

    /**
     * Initialize a default user object.
     *
     * *@return \stdClass user JSON object
     */
    private function getDefaultUser(): \stdClass
    {
        return json_decode('{"name":"", "email":"", "password":"", "secretQuestion":"", "secretResponse":"" }');
    }

    /**
     * Get data type.
     *
     * @return string data type
     */
    private function getDataType(): string
    {
        $datatype = '';
        if (isset($this->verb)) {
            $datatype = $this->verb;
        }
        if (!isset($datatype)) {
            throw new \Exception('Empty datatype');
        }

        return $datatype;
    }

    /**
     * Get path id.
     *
     * @return string id
     */
    private function getId(): string
    {
        $result = '';
        if (isset($this->args) && array_key_exists(0, $this->args)) {
            $result = $this->args[0];
        }

        return $result;
    }

    /**
     * Check config and throw an exception if needed.
     */
    private function checkConfiguration()
    {
        if (!isset($this->conf->{'privatedir'})) {
            throw new \Exception('Empty publicdir');
        }
    }
}