<?php

namespace App\Controller;

use Doctrine\ORM\EntityManager;
use App\Entity\Result;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiResultController
 *
 * @package App\Controller
 *
 * @Route(path=ApiResultController::API_RESULT, name="api_result_")
 */
class ApiResultController extends AbstractController
{

    public const API_RESULT = '/api/v1/results';

    /**
     * @Route(path="", name="getAll", methods={ Request::METHOD_GET })
     * @return JsonResponse
     */
    public function getAll(): JsonResponse
    {
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
     * @Route(path="/{id}", name="get_one_result", methods={ Request::METHOD_GET })+
     * @param Result|null $result
     * @return JsonResponse
     */
    public function findById(?Result $result): JsonResponse
    {
        //        /** @var Result $result */
        //        $result = $this->getDoctrine()
        //            ->getRepository(Result::class)
        //            ->find($id);
        return (null === $result)
            ? $this->error(Response::HTTP_NOT_FOUND, 'NOT FOUND')
            : new JsonResponse(
                $result
            );
    }

    /**
     * @Route(path="", name="post", methods={ Request::METHOD_POST })
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     */
    public function postResult(Request $request): JsonResponse
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $datosPeticion = $request->getContent();
        $datos = json_decode($datosPeticion, true);

        $userId = $datos['user_id'] ?? null;
        $points = $datos['result'] ?? null;
        $time = new \DateTime('now');

        // Error: falta USER
        if (null === $userId) {
            return $this->error(Response::HTTP_UNPROCESSABLE_ENTITY, 'Falta USER');
        }

        // Error: USER no existe
        /** @var User $userPersist */
        $userPersist = $em->getRepository(User::class)->find($userId);
        if (null === $userPersist) {
            return $this->error(Response::HTTP_NOT_FOUND, 'USER no existe');
        }

        // Error: falta RESULT
        if (null === $points) {
            return $this->error(Response::HTTP_UNPROCESSABLE_ENTITY, 'Falta RESULT');
        }

        // Crear Result
        $result = new Result($points, $userPersist, $time);

        // Hacerla persistente
        $em->persist($result);
        $em->flush();

        // devolver respuesta
        return new JsonResponse($result, Response::HTTP_CREATED);
    }

    /**
     * @Route(path="/{id}", name="put", methods={ Request::METHOD_PUT })
     * @param Result|null $result
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     */
    public function putResult(?Result $result, Request $request): JsonResponse
    {
        if (null === $result) {
            return $this->error(Response::HTTP_NOT_FOUND, 'NOT FOUND');
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $datosPeticion = $request->getContent();
        $datos = json_decode($datosPeticion, true);

        $userId = $datos['user'] ?? null;
        $points = $datos['result'] ?? null;
        $time = new \DateTime('now');

        // Error: falta USER
        if (null === $userId) {
            return $this->error(Response::HTTP_UNPROCESSABLE_ENTITY, 'Falta USER');
        }

        // Error: USER no existe
        /** @var User $userPersist */
        $userPersist = $em->getRepository(User::class)->find($userId);
        if (null === $userPersist) {
            return $this->error(Response::HTTP_NOT_FOUND, 'USER no existe');
        }

        // Error: falta RESULT
        if (null === $points) {
            return $this->error(Response::HTTP_UNPROCESSABLE_ENTITY, 'Falta RESULT');
        }

        // Modificar Result
        $result->setResult($points);
        $result->setUser($userPersist);
        $result->setTime($time);

        // Hacerla persistente
        $em->persist($result);
        $em->flush();

        // devolver respuesta
        return new JsonResponse($result, Response::HTTP_OK);
    }

    /**
     * @Route(path="/{id}", name="delete", methods={ Request::METHOD_DELETE })
     * @param Result|null $result
     * @return JsonResponse
     */
    public function deleteOneResult(?Result $result): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        if (null === $result) {
            return $this->error(Response::HTTP_NOT_FOUND, 'NOT FOUND');
        } else {
            $em->remove($result);
            $em->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
    }

    /**
     * @Route(path="", name="deleteAll", methods={ Request::METHOD_DELETE })
     * @return JsonResponse
     */
    public function deleteAllResults(): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Results[] $results */
        $results = $this->getDoctrine()
            ->getRepository(Result::class)
            ->findAll();

        foreach ($results as $result) {
            $em->remove($result);
            $em->flush();
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
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