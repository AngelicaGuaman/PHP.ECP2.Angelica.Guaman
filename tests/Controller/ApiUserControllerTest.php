<?php

namespace MiW\Results\Tests\Controller;

use MiW\Results\Entity\User;
use PHPUnit\Framework\TestCase;

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
            ApiPersonaController::API_USER
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey('personas', $datosRecibidos);
    }

    /**
     *
     * @return int
     */
    public function testPostUser201(): int
    {
        $datos = [
            'username' => 'angelica',
            'email' => 'angelica.@xyz.com',
			'password' => '*angelica*',
			'enabled' => false,
			'admin' => false
        ];
        self::$client->request(
            Request::METHOD_POST,
            ApiUserController::API_USER,
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
        self::assertArrayHasKey('persona', $datosRecibidos);
        self::assertEquals($datos['username'], $datosRecibidos['username']);
		self::assertEquals($datos['email'], $datosRecibidos['email']);
		self::assertEquals($datos['enabled'], $datosRecibidos['enabled']);
		self::assertEquals($datos['isAdmin'], $datosRecibidos['isAdmin']);

        return $datos['id'];
    }

    /**
     * @depends testPostUser201
     * @param int $id
     */
    public function testPostUser400(int $id)
    {
        $datos = [
            'id' => $id
        ];
        self::$client->request(
            Request::METHOD_POST,
            ApiUserController::API_USER,
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
        self::assertArrayHasKey('message', $datosRecibidos);
        self::assertArrayHasKey('code', $datosRecibidos['message']);
    }

    /**
     * Implements testGetPersona200
     * @depends testPostPersona201
     * @covers ::getPersona
     * @param int $dni
     */
    public function testGetPersona200(int $dni)
    {
        self::$client->request(
            Request::METHOD_GET,
            ApiPersonaController::API_PERSONA . '/' . $dni
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );
        self::assertJson($response->getContent());
        $datosRecibidos = json_decode($response->getContent(), true);
        self::assertArrayHasKey('persona', $datosRecibidos);
        self::assertArrayHasKey('dni', $datosRecibidos['persona']);
        self::assertEquals($dni, $datosRecibidos['persona']['dni']);
    }

    /**
     * Implements testGetPersona404
     * @covers ::getPersona
     * @covers ::error
     */
    public function testGetPersona404()
    {
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Proveedor de datos de persona
     * @return array
     */
    public function proveedorPersonas(): array
    {
        return [
           'user1' => [ '876132504', 'nombre,hbchdc', 'hgdsakf@xyz.com' ],
        ];
    }
}
