<?php

namespace App\Tests\Controller;

use App\Controller\ApiResultController;
use App\Controller\UsersController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiResultControllerTest
 *
 * @package App\Tests\Controller
 * @coversDefaultClass \App\Controller\ApiResultControllerTest
 */
class ApiResultControllerTest extends WebTestCase
{
    /** @var Client $client */
    private static $client;

    public static function setUpBeforeClass()
    {
        self::$client = static::createClient();
    }

    /**
     * Implements testGetAllResults200
     * @covers ::getAll
     */
    public function testGetAllResults200()
    {
        self::$client->request(
            Request::METHOD_GET,
            ApiResultController::API_RESULT
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey('results', $datosRecibidos);
        dump($datosRecibidos, '<<<<<< GET ALL RESULTS 200');
    }

    /**
     * Implements testPostUser201
     * @covers ::postUser
     * @return int
     */
    public function testPostUser201(): int
    {
        $datos = [
            'username' => '555angelica',
            'email' => '555angelica.@xyz.com',
            'password' => '*angelica*',
            'enabled' => false,
            'admin' => false
        ];
        self::$client->request(
            Request::METHOD_POST,
            UsersController::API_USER,
            [], [], [], json_encode($datos)
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_CREATED,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertEquals($datos['username'], $datosRecibidos['username']);
        self::assertEquals($datos['email'], $datosRecibidos['email']);
        self::assertEquals($datos['enabled'], $datosRecibidos['enabled']);
        self::assertEquals($datos['admin'], $datosRecibidos['admin']);

        dump($datosRecibidos, '<<<<<< POST USER 201');
        return $datosRecibidos['id'];
    }

    /**
     * Implements testPostResult201
     * @depends testPostUser201
     * @covers ::postResult
     * @return int
     */
    public function testPostResult201(int $id): int
    {
        $datos = [
            'user_id' => $id,
            'result' => 29,
        ];
        self::$client->request(
            Request::METHOD_POST,
            ApiResultController::API_RESULT,
            [], [], [], json_encode($datos)
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_CREATED,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertEquals($datos['user_id'], $datosRecibidos['user_id']);
        self::assertEquals($datos['result'], $datosRecibidos['result']);

        dump($datosRecibidos, '<<<<<< POST RESULT 201');
        return $datosRecibidos['id'];
    }

    /**
     * Implements testPostUser422
     * @depends testPostUser201
     * @covers ::postUser
     * @covers ::error
     */
    public function testPostUser422(): void
    {
        $datos = [
            'email' => '555angelica.@xyz.com',
            'password' => '*angelica*',
            'enabled' => false,
            'admin' => false
        ];
        self::$client->request(
            Request::METHOD_POST,
            ApiResultController::API_RESULT,
            [], [], [], json_encode($datos)
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertEquals(422, $datosRecibidos["message"]["code"]);
        self::assertEquals("Falta USERNAME", $datosRecibidos["message"]["message"]);

        dump($datosRecibidos, '<<<<<< POST USER 422');
    }

    /**
     * Implements testPostUser400
     * @depends testPostUser201
     * @covers ::postUser
     * @covers ::error
     */
    public function testPostUser400(): void
    {
        $datos = [
            'username' => '555angelica',
            'email' => '555angelica.@xyz.com',
            'password' => '*angelica*',
            'enabled' => false,
            'admin' => false
        ];
        self::$client->request(
            Request::METHOD_POST,
            ApiResultController::API_RESULT,
            [], [], [], json_encode($datos)
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_BAD_REQUEST,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertEquals(Response::HTTP_BAD_REQUEST, $datosRecibidos["message"]["code"]);
        self::assertEquals("USERNAME ya existe", $datosRecibidos["message"]["message"]);

        dump($datosRecibidos, '<<<<<< POST USER 400');
    }


    /**
     * Implements testGetUser200
     * @depends testPostUser201
     * @covers ::findById
     * @param int $id
     * @return array $user
     */
    public function testGetUser200(int $id): array
    {
        self::$client->request(
            Request::METHOD_GET,
            ApiResultController::API_RESULT . '/' . $id
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey('id', $datosRecibidos);
        self::assertArrayHasKey('username', $datosRecibidos);
        self::assertArrayHasKey('email', $datosRecibidos);
        self::assertArrayHasKey('enabled', $datosRecibidos);
        self::assertArrayHasKey('admin', $datosRecibidos);
        self::assertEquals($id, $datosRecibidos['id']);

        dump($datosRecibidos, '<<<<<< GET USER 200');

        return $datosRecibidos;
    }

    /**
     * Implements testGetUser404
     * @covers ::findById
     * @covers ::error
     */
    public function testGetUser404()
    {
        $id = -1;

        self::$client->request(
            Request::METHOD_GET,
            ApiResultController::API_RESULT . '/' . $id
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_NOT_FOUND,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertEquals(404, $datosRecibidos["message"]["code"]);
        self::assertEquals("NOT FOUND", $datosRecibidos["message"]["message"]);

        dump($datosRecibidos, '<<<<<< GET USER 404');
    }

    /**
     * Implements testPutUser400
     * @depends testGetUser200
     * @covers ::putUser
     * @covers ::error
     * @param array $user
     */
    public function testPutUser400(array $user): void
    {
        $id = $user['id'];

        $datos = [
            'username' => '555angelica',
            'email' => '555angelica.@xyz.com',
            'password' => '*angelica*',
            'enabled' => false,
            'admin' => false
        ];
        self::$client->request(
            Request::METHOD_PUT,
            ApiResultController::API_RESULT . '/' . $id,
            [], [], [], json_encode($datos)
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_BAD_REQUEST,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertEquals(Response::HTTP_BAD_REQUEST, $datosRecibidos["message"]["code"]);
        self::assertEquals("USERNAME ya existe", $datosRecibidos["message"]["message"]);

        dump($datosRecibidos, '<<<<<< PUT USER 400');
    }

    /**
     * Implements testPutUser200
     * @depends testGetUser200
     * @covers ::putUser
     * @param array $user
     */
    public function testPutUser200(array $user): void
    {
        $id = $user['id'];

        $datos = [
            'username' => 'new_angelica',
            'email' => 'new_angelica.@xyz.com',
            'password' => 'new_*angelica*',
            'enabled' => false,
            'admin' => false
        ];
        self::$client->request(
            Request::METHOD_PUT,
            ApiResultController::API_RESULT . '/' . $id,
            [], [], [], json_encode($datos)
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertEquals($datos['username'], $datosRecibidos['username']);
        self::assertEquals($datos['email'], $datosRecibidos['email']);
        self::assertEquals($datos['enabled'], $datosRecibidos['enabled']);
        self::assertEquals($datos['admin'], $datosRecibidos['admin']);

        dump($datosRecibidos, '<<<<<< PUT USER 200');
    }

    /**
     * Implements testPutUser422
     * @depends testGetUser200
     * @covers ::putUser
     * @covers ::error
     * @param array $user
     */
    public function testPutUser422(array $user): void
    {
        $id = $user['id'];

        $datos = [
            'email' => '555angelica.@xyz.com',
            'password' => '*angelica*',
            'enabled' => false,
            'admin' => false
        ];
        self::$client->request(
            Request::METHOD_PUT,
            ApiResultController::API_RESULT . '/' . $id,
            [], [], [], json_encode($datos)
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $datosRecibidos["message"]["code"]);
        self::assertEquals("Falta USERNAME", $datosRecibidos["message"]["message"]);

        dump($datosRecibidos, '<<<<<< PUT USER 422');
    }


    /**
     * Implements testPutUser404
     * @covers ::putUser
     * @covers ::error
     */
    public function testPutUser404()
    {
        $id = -1;

        self::$client->request(
            Request::METHOD_PUT,
            ApiResultController::API_RESULT . '/' . $id
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_NOT_FOUND,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertEquals(Response::HTTP_NOT_FOUND, $datosRecibidos["message"]["code"]);
        self::assertEquals("NOT FOUND", $datosRecibidos["message"]["message"]);

        dump($datosRecibidos, '<<<<<< PUT USER 404');
    }

    /**
     * Implements testDeleteUser204
     * @depends testPostUser201
     * @covers ::deleteOneUser
     * @param int $id
     */
    public function testDeleteUser204(int $id): void
    {
        self::$client->request(
            Request::METHOD_DELETE,
            ApiResultController::API_RESULT . '/' . $id
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode()
        );
        self:
        self::assertEquals("", $response->getContent());
        dump($response->getContent(), '<<<< DELETE USER 204');
    }

    /**
     * Implements testDeleteUser404
     * @covers ::deleteOneUser
     */
    public function testDeleteUser404(): void
    {
        $id = -1;

        self::$client->request(
            Request::METHOD_DELETE,
            ApiResultController::API_RESULT . '/' . $id
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_NOT_FOUND,
            $response->getStatusCode()
        );

        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertEquals(Response::HTTP_NOT_FOUND, $datosRecibidos["message"]["code"]);
        self::assertEquals("NOT FOUND", $datosRecibidos["message"]["message"]);
        dump($response->getContent(), '<<<< DELETE USER 204');
    }

    /**
     * Implements testDeleteUsers204
     * @depends testPostUser201
     * @covers ::deleteAllUsers
     */
    public function testDeleteUsers204(): void
    {
        self::$client->request(
            Request::METHOD_DELETE,
            ApiResultController::API_RESULT
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode()
        );
        self:
        self::assertEquals("", $response->getContent());
        dump($response->getContent(), '<<<< DELETE ALL USERS 204');
    }
}
