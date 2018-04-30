<?php

declare(strict_types=1);
namespace mobilecms\api;

use PHPUnit\Framework\TestCase;

final class AuthenticationApiTest extends ApiTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->API=new AuthenticationApi();
        $this->API->loadConf(realpath('tests/conf.json'));
        $this->API->setRootDir(realpath('tests-data')); // unit test only
    }


    public function testEmptyConf()
    {
        $this->API->loadConf('tests/partial.json');
        $this->path = '/api/v1/authenticate';
        $recordStr = '{ "user": "test@example.com", "password":"Sample#123456"}';

        $this->REQUEST = ['path' => $this->path];

        $response = $this->request('POST', $this->path);

        $this->POST = ['requestbody' => $recordStr];



        $result = $response->getResult();
        $this->assertEquals(401, $response->getCode());
    }

    public function testAuthenticateOptions()
    {
        $this->path = '/api/v1/authenticate';
        $response = $this->request('OPTIONS', $this->path);




        $result = $response->getResult();

        $this->assertTrue($result != null);
        $this->assertJsonStringEqualsJsonString('{}', $result);
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
    }

    public function testRegisterOptions()
    {
        $this->path = '/api/v1/register';
        $response = $this->request('OPTIONS', $this->path);




        $result = $response->getResult();

        $this->assertTrue($result != null);
        $this->assertJsonStringEqualsJsonString('{}', $result);
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
    }

    public function testResetPasswordOptions()
    {
        $this->path = '/api/v1/resetpassword';
        $response = $this->request('OPTIONS', $this->path);




        $result = $response->getResult();

        $this->assertTrue($result != null);
        $this->assertJsonStringEqualsJsonString('{}', $result);
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
    }
    public function testChangePasswordOptions()
    {
        $this->path = '/api/v1/changepassword';
        $response = $this->request('OPTIONS', $this->path);




        $result = $response->getResult();

        $this->assertTrue($result != null);
        $this->assertJsonStringEqualsJsonString('{}', $result);
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
    }

    public function testAuthenticateNoBody()
    {
        $this->path = '/api/v1/authenticate';
        $this->REQUEST = ['path' => $this->path];
        $response = $this->request('POST', $this->path);



        $result = $response->getResult();
        $this->assertEquals(401, $response->getCode());
        $this->assertTrue($result != null && $result != '');
    }

    public function testAuthenticateEmptyBody()
    {
        $this->path = '/api/v1/authenticate';
        $recordStr = '{ "user": "test@example.com", "password":"Sample#123456"}';

        $this->REQUEST = ['path' => $this->path];

        $response = $this->request('POST', $this->path);

        $this->POST = ['requestbody' => ''];



        $result = $response->getResult();
        $this->assertEquals(401, $response->getCode());
        $this->assertTrue($result != null && $result != '');
    }

    public function testLoginByUser()
    {
        $this->path = '/api/v1/authenticate';
        $recordStr = '{ "user": "test@example.com", "password":"Sample#123456"}';

        $this->REQUEST = ['path' => $this->path];



        $this->POST = ['requestbody' => $recordStr];
        $response = $this->request('POST', $this->path);

        $result = $response->getResult();
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($result != null && $result != '');

        $userObject = json_decode($result);

        $this->assertTrue($userObject->{'email'} === 'test@example.com');
        $this->assertTrue(strlen($userObject->{'token'}) > 150);
    }

    public function testLoginByEmail()
    {
        $this->path = '/api/v1/authenticate';
        $recordStr = '{ "email": "test@example.com", "password":"Sample#123456"}';

        $this->REQUEST = ['path' => $this->path];


        $this->POST = ['requestbody' => $recordStr];

        $response = $this->request('POST', $this->path);

        $result = $response->getResult();
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($result != null && $result != '');

        $userObject = json_decode($result);

        $this->assertTrue($userObject->{'email'} === 'test@example.com');
        $this->assertTrue(strlen($userObject->{'token'}) > 150);
    }

    public function testNoPassword()
    {
        $this->path = '/api/v1/authenticate';
        $recordStr = '{ "user": "test@example.com"}';

        $this->REQUEST = ['path' => $this->path];



        $this->POST = ['requestbody' => $recordStr];

        $response = $this->request('POST', $this->path);

        $result = $response->getResult();
        $this->assertEquals(401, $response->getCode());
    }

    public function testEmptyPassword()
    {
        $this->path = '/api/v1/authenticate';
        $recordStr = '{ "user": "test@example.com", "password":""}';

        $this->REQUEST = ['path' => $this->path];



        $this->POST = ['requestbody' => $recordStr];

        $response = $this->request('POST', $this->path);

        $result = $response->getResult();
        $this->assertEquals(401, $response->getCode());
    }

    public function testEmptyUser()
    {
        $this->path = '/api/v1/authenticate';
        $recordStr = '{ "user": "","password":"foo"}';

        $this->REQUEST = ['path' => $this->path];



        $this->POST = ['requestbody' => $recordStr];

        $response = $this->request('POST', $this->path);

        $result = $response->getResult();
        $this->assertEquals(401, $response->getCode());
    }

    public function testRegister()
    {
        $this->path = '/api/v1/register';

        $email = 'testregister@example.com';

        $file = $this->API->getPrivateDirPath() . '/users/' . $email . '.json';
        if (file_exists($file)) {
            unlink($file);
        }



        $recordStr = '{ "name": "test register", "email": "testregister@example.com", "password":"Sample#123456", "secretQuestion": "some secret" , "secretResponse": "secret response"}';

        $this->REQUEST = ['path' => $this->path];



        $this->POST = ['requestbody' => $recordStr];

        $response = $this->request('POST', $this->path);

        $result = $response->getResult();
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($result != null && $result != '');


        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function testRegisterEmptyParam()
    {
        $this->path = '/api/v1/register';

        $email = 'testregister@example.com';

        $file = $this->API->getPrivateDirPath() . '/users/' . $email . '.json';
        if (file_exists($file)) {
            unlink($file);
        }



        $recordStr = '{ "name": "test register", "email": "", "password":""}';

        $this->REQUEST = ['path' => $this->path];


        $this->POST = ['requestbody' => $recordStr];

        $response = $this->request('POST', $this->path);

        $result = $response->getResult();

        $this->assertEquals(400, $response->getCode());
        $this->assertTrue($result != null && $result != '');
    }

    public function testResetPassword()
    {
        $this->path = '/api/v1/resetpassword';
        $user = 'resetpassword@example.com';
        $userFile = $user . '.json';

        copy($this->API->getPrivateDirPath() . '/save/' . $userFile, $this->API->getPrivateDirPath() . '/users/' . $userFile);

        $recordStr = '{ "user": "' . $user . '", "password":"Sample#123456", "newpassword":"Foobar!654321"}';

        $this->REQUEST = ['path' => $this->path];



        $this->POST = ['requestbody' => $recordStr];

        $response = $this->request('POST', $this->path);

        $result = $response->getResult();

        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($result != null && $result != '');

        $userObject = json_decode($result);

        $this->assertTrue($userObject->{'name'} === $user);
        $this->assertTrue($userObject->{'clientalgorithm'} === 'none');
        $this->assertTrue($userObject->{'newpasswordrequired'} === 'true');
        $this->assertTrue($userObject->{'notification'} != '');



        $this->assertContains('DOCTYPE', $userObject->{'notification'});
        $this->assertContains('meta charset', $userObject->{'notification'});
        $this->assertContains('Password', $userObject->{'notification'});
        $this->assertContains('Connection info', $userObject->{'notification'});

        // delete file
        unlink($this->API->getPrivateDirPath() . '/users/' . $userFile);
    }

    public function testChangePassword()
    {
        $this->path = '/api/v1/changepassword';
        $user = 'changepassword@example.com';
        $userFile = $user . '.json';

        copy($this->API->getPrivateDirPath() . '/save/' . $userFile, $this->API->getPrivateDirPath() . '/users/' . $userFile);

        $recordStr = '{ "user": "' . $user . '", "password":"Sample#123456", "newpassword":"Foobar!654321"}';

        $this->REQUEST = ['path' => $this->path];

        $this->POST = ['requestbody' => $recordStr];
        $response = $this->request('POST', $this->path);
        $result = $response->getResult();
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($result != null && $result != '');

        // test new password with login
        $loginRecordStr = '{ "email": "' . $user . '", "password":"Foobar!654321"}';

        $recordStr = '{ "user": "changepassword@example.com", "password":"Foobar!654321"}';

        $this->verifyChangePassword($user, $recordStr);

        // delete file
        unlink($this->API->getPrivateDirPath() . '/users/' . $userFile);
    }

    public function verifyChangePassword($user, $recordStr)
    {
        $this->path = '/api/v1/authenticate';

        $this->REQUEST = ['path' => $this->path];
        $this->POST = ['requestbody' => $recordStr];
        $response = $this->request('POST', $this->path);
        $result = $response->getResult();
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($result != null && $result != '');

        $userObject = json_decode($result);

        $this->assertTrue($userObject->{'email'} === $user);
        $this->assertTrue(strlen($userObject->{'token'}) > 150);
    }

    public function testPublicInfo()
    {
        $this->path = '/api/v1/publicinfo/editor@example.com';

        $this->REQUEST = ['path' => $this->path];

        $response = $this->request('GET', $this->path);




        $result = $response->getResult();
        $this->printError($response);
        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($result != null && $result != '');

        $userObject = json_decode($result);

        $this->assertTrue($userObject->{'name'} === 'editor@example.com');
        $this->assertTrue($userObject->{'clientalgorithm'} === 'hashmacbase64');
        $this->assertTrue($userObject->{'newpasswordrequired'} === 'false');
    }
}
