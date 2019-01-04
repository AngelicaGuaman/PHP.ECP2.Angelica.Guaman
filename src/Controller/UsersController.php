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
 * Class UsersController
 *
 * @package App\Controller
 *
 * @Route(path=UsersController::API_USER, name="api_user_")
 */
class UsersController extends AbstractController
{

    public const API_USER = '/api/v1/users';

    /**
     * @Route(path="", name="getAll", methods={ Request::METHOD_GET })
     * @return JsonResponse
     */
    public function getAll(): JsonResponse
    {
        /** @var User[] $users */
        $users = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();
        return (null === $users)
            ? $this->error(Response::HTTP_NOT_FOUND, 'NOT FOUND')
            : new JsonResponse(
                ['users' => $users]
            );
    }

    /**
     * @Route(path="/{id}", name="get_one_user", methods={ Request::METHOD_GET })+
     * @param User|null $user
     * @return JsonResponse
     */
    public function findById(?User $user): JsonResponse
    {
        ///** @var User $user */
        //$user = $this->getDoctrine()
        //  ->getRepository(User::class)
        //  ->find($id);
        return (null === $user)
            ? $this->error(Response::HTTP_NOT_FOUND, 'NOT FOUND')
            : new JsonResponse(
                $user
            );
    }

    /**
     * @Route(path="", name="post", methods={ Request::METHOD_POST })
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     */
    public function postUser(Request $request): JsonResponse
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $datosPeticion = $request->getContent();
        $datos = json_decode($datosPeticion, true);

        $username = $datos['username'] ?? null;
        $email = $datos['email'] ?? null;
        $enabled = $datos['enabled'] ?? null;
        $password = $datos['password'] ?? null;
        $admin = $datos['admin'] ?? false;

        // Error: falta USERNAME
        if (null === $username) {
            return $this->error(Response::HTTP_UNPROCESSABLE_ENTITY, 'Falta USERNAME');
        }

        // Error: USERNAME ya existe
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneBy(['username' => $username]);
        if (null !== $user) {
            return $this->error(Response::HTTP_BAD_REQUEST, 'USERNAME ya existe');
        }

        // Error: falta EMAIL
        if (null === $email) {
            return $this->error(Response::HTTP_UNPROCESSABLE_ENTITY, 'Falta EMAIL');
        }

        // Error: EMAIL ya existe
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        if (null !== $user) {
            return $this->error(Response::HTTP_BAD_REQUEST, 'EMAIL ya existe');
        }

        // Error: falta PASSWORD
        if (null === $password) {
            return $this->error(Response::HTTP_UNPROCESSABLE_ENTITY, 'Falta PASSWORD');
        }

        // Error: falta ENABLED
        if (null === $enabled) {
            return $this->error(Response::HTTP_UNPROCESSABLE_ENTITY, 'Falta ENABLED');
        }

        // Crear User
        $user = new User($username, $email, $password, $enabled, $admin);

        // Hacerla persistente
        $em->persist($user);
        $em->flush();

        // devolver respuesta
        return new JsonResponse($user, Response::HTTP_CREATED);
    }

    /**
     * @Route(path="/{id}", name="put", methods={ Request::METHOD_PUT })
     * @param User|null $user
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     */
    public function putUser(?User $user, Request $request): JsonResponse
    {
        if (null === $user) {
            return $this->error(Response::HTTP_NOT_FOUND, 'NOT FOUND');
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $datosPeticion = $request->getContent();
        $datos = json_decode($datosPeticion, true);

        $username = $datos['username'] ?? null;
        $email = $datos['email'] ?? null;
        $enabled = $datos['enabled'] ?? null;
        $password = $datos['password'] ?? null;
        $admin = $datos['admin'] ?? false;

        // Error: falta USERNAME
        if (null === $username) {
            return $this->error(Response::HTTP_UNPROCESSABLE_ENTITY, 'Falta USERNAME');
        }

        // Error: USERNAME ya existe
        /** @var User $userPersist */
        $userPersist = $em->getRepository(User::class)->findOneBy(['username' => $username]);
        if (null !== $userPersist) {
            return $this->error(Response::HTTP_BAD_REQUEST, 'USERNAME ya existe');
        }

        // Error: falta EMAIL
        if (null === $email) {
            return $this->error(Response::HTTP_UNPROCESSABLE_ENTITY, 'Falta EMAIL');
        }

        // Error: EMAIL ya existe
        /** @var User $userPersist */
        $userPersist = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        if (null !== $userPersist) {
            return $this->error(Response::HTTP_BAD_REQUEST, 'EMAIL ya existe');
        }

        // Error: falta PASSWORD
        if (null === $password) {
            return $this->error(Response::HTTP_UNPROCESSABLE_ENTITY, 'Falta PASSWORD');
        }

        // Error: falta ENABLED
        if (null === $enabled) {
            return $this->error(Response::HTTP_UNPROCESSABLE_ENTITY, 'Falta ENABLED');
        }

        // Modificar User
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($password);
        $user->setEnabled($enabled);
        $user->setIsAdmin($admin);

        // Hacerla persistente
        $em->persist($user);
        $em->flush();

        // devolver respuesta
        return new JsonResponse($user, Response::HTTP_OK);
    }

    /**
     * @Route(path="/{id}", name="delete", methods={ Request::METHOD_DELETE })
     * @param User|null $user
     * @return JsonResponse
     */
    public function deleteOneUser(?User $user): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        if (null === $user) {
            return $this->error(Response::HTTP_NOT_FOUND, 'NOT FOUND');
        } else {
            $em->remove($user);
            $em->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
    }

    /**
     * @Route(path="", name="deleteAll", methods={ Request::METHOD_DELETE })
     * @return JsonResponse
     */
    public function deleteAllUsers(): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();

        /** @var User[] $users */
        $users = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();

        foreach ($users as $user) {
            $em->remove($user);
            $em->flush();
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route(path="", name="options", methods={ Request::METHOD_OPTIONS })
     * @return JsonResponse
     */
    public function optionsAllUsers(): JsonResponse
    {
        return new JsonResponse(null, Response::HTTP_OK, array("Allow" => "GET, POST, DELETE, OPTIONS"));
    }
	
	/**
     * @Route(path="/{id}", name="options_id", methods={ Request::METHOD_OPTIONS })
     * @return JsonResponse
     */
    public function optionsUser(): JsonResponse
    {
        return new JsonResponse(null, Response::HTTP_OK, array("Allow" => "GET, PUT, DELETE, OPTIONS"));
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
