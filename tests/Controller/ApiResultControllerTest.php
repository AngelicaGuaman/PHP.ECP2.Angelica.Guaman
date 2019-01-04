<?php

namespace App\Tests\Controller;

use App\Controller\ApiResultController;
use App\Controller\UsersController;
use App\Entity\User;
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
	
	/** @var User $user */
    private static $user;

    public static function setUpBeforeClass()
    {
        self::$client = static::createClient();
		self::$user = static::createUser();
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
     * Implements testPostResult201
     * @covers ::postResult
     * @return int
     */
    public function testPostResult201(): int
    {
        $datos = [
            'user_id' => self::$user['id'],
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
     * Implements testPostResult422
     * @covers ::postResult
     * @covers ::error
     */
    public function testPostResult422(): void
    {
        $datos = [
            'user_id' => self::$user['id']
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
        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $datosRecibidos["message"]["code"]);
        self::assertEquals("Falta RESULT", $datosRecibidos["message"]["message"]);

        dump($datosRecibidos, '<<<<<< POST RESULT 422');
    }

    /**
     * Implements testPostResult404
     * @covers ::postResult
     * @covers ::error
     */
    public function testPostResult404(): void
    {
        $datos = [
            'user_id' => -1,
            'result' => 555
        ];
		
        self::$client->request(
            Request::METHOD_POST,
            ApiResultController::API_RESULT,
            [], [], [], json_encode($datos)
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
        self::assertEquals("USER no existe", $datosRecibidos["message"]["message"]);

        dump($datosRecibidos, '<<<<<< POST RESULT 404');
    }


    /**
     * Implements testGetUser200
     * @depends testPostResult201
     * @covers ::findById
     * @param int $id
     * @return array $result
     */
    public function testGetResult200(int $id): array
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
        self::assertArrayHasKey('result', $datosRecibidos);
        self::assertArrayHasKey('time', $datosRecibidos);
        self::assertArrayHasKey('user', $datosRecibidos);
        self::assertEquals($id, $datosRecibidos['id']);

        dump($datosRecibidos, '<<<<<< GET RESULT 200');

        return $datosRecibidos;
    }

    /**
     * Implements testGetResult404
     * @covers ::findById
     * @covers ::error
     */
    public function testGetResult404()
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
        self::assertEquals(Response::HTTP_NOT_FOUND, $datosRecibidos["message"]["code"]);
        self::assertEquals("NOT FOUND", $datosRecibidos["message"]["message"]);

        dump($datosRecibidos, '<<<<<< GET RESULT 404');
    }

    /**
     * Implements testPutResult404
     * @covers ::putResult
     * @covers ::error
     */
    public function testPutResult404ResultNotFound(): void
    {
        $id = -1;

        $datos = [
            'user_id' => self::$user['id'],
            'rsult' => 555
        ];
		
        self::$client->request(
            Request::METHOD_PUT,
            ApiResultController::API_RESULT . '/' . $id,
            [], [], [], json_encode($datos)
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
        self::assertEquals("RESULT NOT FOUND", $datosRecibidos["message"]["message"]);

        dump($datosRecibidos, '<<<<<< PUT RESULT 404 RESULT NOT FOUND');
    }
	
	/**
     * Implements testPutResult404
     * @depends testGetResult200
     * @covers ::putResult
     * @covers ::error
     * @param array $result
     */
    public function testPutResult404UserNotFound(array $result): void
    {
        $id = $result['id'];

        $datos = [
            'user_id' => -1,
            'rsult' => 555
        ];
		
        self::$client->request(
            Request::METHOD_PUT,
            ApiResultController::API_RESULT . '/' . $id,
            [], [], [], json_encode($datos)
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
        self::assertEquals("USER no existe", $datosRecibidos["message"]["message"]);

        dump($datosRecibidos, '<<<<<< PUT RESULT 404 USER NOT FOUND');
    }

    /**
     * Implements testPutResult200
     * @depends testGetResult200
     * @covers ::putResult
     * @param array $result
     */
    public function testPutResult200(array $result): void
    {
        $id = $result['id'];

        $datos = [
            'user_id' => self::$user['id'],
            'rsult' => 555
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
        self::assertEquals($datos['user_id'], $datosRecibidos['user']['id']);
        self::assertEquals($datos['result'], $datosRecibidos['result']);

        dump($datosRecibidos, '<<<<<< PUT RESULT 200');
    }

    /**
     * Implements testPutResult422
     * @depends testGetResult200
     * @covers ::putResult
     * @covers ::error
     * @param array $result
     */
    public function testPutResult422(array $result): void
    {
        $id = $result['id'];

        $datos = [
            'user_id' => self::$user['id']
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
        self::assertEquals("Falta RESULT", $datosRecibidos["message"]["message"]);

        dump($datosRecibidos, '<<<<<< PUT RESULT 422');
    }

    /**
     * Implements testDeleteResult204
     * @depends testPostResult201
     * @covers ::deleteOneResult
     * @param int $id
     */
    public function testDeleteResult204(int $id): void
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
        dump($response->getContent(), '<<<< DELETE RESULT 204');
    }

    /**
     * Implements testDeleteResult404
     * @covers ::deleteOneResult
     */
    public function testDeleteResult404(): void
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
        dump($response->getContent(), '<<<< DELETE RESULT 404');
    }

    /**
     * Implements testDeleteResults204
     * @depends testPostResult201
     * @covers ::deleteAllResults
     */
    public function testDeleteResults204(): void
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
        dump($response->getContent(), '<<<< DELETE ALL RESULT 204');
    }
	
	 /**
     * @return int
     */
    public static function createUser(): array
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
        $user = json_decode($response->getContent(), true);
  
        dump($datosRecibidos, '<<<<<< POST USER PRUEBA');
        return $user;
    }
	
	public static function tearDownAfterClass()
    {
		 self::$client->request(
            Request::METHOD_DELETE,
            UsersController::API_USER
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode()
        );
        self:self::assertEquals("", $response->getContent());
        dump($response->getContent(), '<<<< DELETE ALL USERS 204');
	}
