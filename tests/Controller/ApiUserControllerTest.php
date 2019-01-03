<?php

namespace App\Tests\Controller;

use App\Controller\UsersController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiUserControllerTest
 *
 * @package App\Tests\Controller
 * @coversDefaultClass \App\Controller\ApiUserControllerTest
 */
class ApiUserControllerTest extends WebTestCase
{
    /** @var Client $client */
    private static $client;

    public static function setUpBeforeClass()
    {
        self::$client = static::createClient();
    }

    /**
     * Implements testGetAllUsers200
     * @covers ::getAll
     */
    public function testGetAllUsers200()
    {
        self::$client->request(
            Request::METHOD_GET,
            UsersController::API_USER
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey('users', $datosRecibidos);
        dump($datosRecibidos, '<<<<<< GET ALL USERS 200');
    }

    /**
     * @Implements testPostUser201
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

        dump($datosRecibidos, '<<<<<< POST USER 200');
        return $datosRecibidos['id'];
    }

    /**
     * @Implements testPostUser422
     * @depends testPostUser201
     * @covers ::postUser
     * @covers ::error
     */
    public function testPostUser422(): void
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
     * @Implements testGetUser200
     * @depends testPostUser201
     * @covers ::findById
     * @param int $id
     * @return array $user
     */
    public function testGetUser200(int $id): array
    {
        self::$client->request(
            Request::METHOD_GET,
            UsersController::API_USER . '/' . $id
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
            UsersController::API_USER . '/' . $id
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


}
