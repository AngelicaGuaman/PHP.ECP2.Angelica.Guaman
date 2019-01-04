<?php

namespace App\Controller;

use Doctrine\ORM\EntityManager;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiUserResultController
 *
 * @package App\Controller
 *
 * @Route(path=UsersController::API_USER, name="api_user_results_")
 */
class ApiUserResultController extends AbstractController
{

    public const API_USER = '/api/v1/users';

    /**
     * @Route(path="/{id}/results", name="getAll", methods={ Request::METHOD_GET })
     * @return JsonResponse
     */
    public function getAllResultByUserId(?User $user): JsonResponse
    {
		if (null === $user) {
            return $this->error(Response::HTTP_NOT_FOUND, 'USER no existe');
        }
		
        /** @var Result[] $results */
        $results = $this->getDoctrine()
            ->getRepository(Result::class)
            ->findAll();
        return (null === $results)
            ? $this->error(Response::HTTP_NOT_FOUND, 'NOT FOUND')
            : new JsonResponse(
                ['results' => $results]
            );
    }

    /**
     * @Route(path="/{id}/results", name="deleteAll", methods={ Request::METHOD_DELETE })
     * @return JsonResponse
     */
    public function deleteAllResultByUserId(?User $user): JsonResponse
    {
		if (null === $user) {
            return $this->error(Response::HTTP_NOT_FOUND, 'USER no existe');
        }
		
        $em = $this->getDoctrine()->getManager();

        /** @var Result[] $results */
        $results = $this->getDoctrine()
            ->getRepository(Result::class)
            ->findBy(['user_id' => $user['id']]);

        foreach ($results as $result) {
            $em->remove($result);
            $em->flush();
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
	
	/**
     * @Route(path="/{id}/results", name="options", methods={ Request::METHOD_OPTIONS })
     * @return JsonResponse
     */
    public function optionsUserResults(?User $user): JsonResponse
    {
		if (null === $user) {
            return $this->error(Response::HTTP_NOT_FOUND, 'USER no existe');
        }
		
        return new JsonResponse(null, Response::HTTP_OK, array("Allow" => "GET, DELETE, OPTIONS"));
    }
	

    /**
     * @param int $statusCode
     * @param string $message
     *
     * @return JsonResponse
     */
    private function error(int $statusCode, string $message): JsonResponse
    {
        return new JsonResponse(
            [
                'message' => [
                    'code' => $statusCode,
                    'message' => $message
                ]
            ],
            $statusCode
        );
    }


}
