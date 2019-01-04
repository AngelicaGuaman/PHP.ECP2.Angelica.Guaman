<?php

namespace App\Tests\Controller;

use App\Controller\UsersController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiUserResultsControllerTest
 *
 * @package App\Tests\Controller
 * @coversDefaultClass \App\Controller\ApiUserResultsControllerTest
 */
class ApiUserResultsControllerTest extends WebTestCase
{
    /** @var Client $client */
    private static $client;
	
	/** @var User $user */
    private static $user;
	
	/** @var Result $result */
    private static $result;

    public static function setUpBeforeClass()
    {
        self::$client = static::createClient();
		self::$user = static::createUser();
		self::$result = static::createResult();
    }


    /**
     * Implements testGetAllResultsByUserId200
     * @covers ::getAllResultByUserId
     */
    public function testGetAllResultsByUserId200()
    {
		$userid = self::$user['id'];
		
        self::$client->request(
            Request::METHOD_GET,
            ApiUserResultsController::API_USER . '/' . $userId .'/results'
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
     * Implements testGetAllResultsByUserId404
     * @covers ::getAllResultByUserId
	 * @covers ::error
     */
    public function testGetAllResultsByUserId404()
    {
		$userId = -1; 
        self::$client->request(
            Request::METHOD_GET,
            ApiUserResultsController::API_USER . '/' . $userId .'/results'
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
		
		dump($datosRecibidos, '<<<<<< GET RESULTS BY USER_ID 404');
    }
	
	/**
     * Implements testDeleteResultsByUserId204
     * @covers ::deleteAllResultByUserId
     */
    public function testDeleteResultsByUserId204(): void
    {
		$userid = self::$user['id'];
		
        self::$client->request(
            Request::METHOD_DELETE,
            ApiUserResultsController::API_USER . '/' . $userId .'/results'
        );
        /** @var Response $response */
        $response = self::$client->getResponse();
        self::assertEquals(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode()
        );
        self:self::assertEquals("", $response->getContent());
        dump($response->getContent(), '<<<< DELETE RESULTS BY USER_ID 204');
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
	
	/**
     * @return int
     */
    public function createResult(): array
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
        $result = json_decode($response->getContent(), true);

        dump($datosRecibidos, '<<<<<< POST RESULT PRUEBA');
        return $result;
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
}
