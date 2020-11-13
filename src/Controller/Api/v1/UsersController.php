<?php

namespace App\Controller\Api\v1;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/users")
 */
class UsersController extends AbstractController
{
    /**
     * @Route("/", methods={"POST"})
     * @param Request                      $request
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface       $entityManager
     * @return JsonResponse
     */
    public function create(Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $entityManager): Response
    {
        // todo: json validation, access rights, error handling

        $jsonData = json_decode($request->getContent());

        $user = new User();
        $user->setUsername($jsonData->username)
             ->setPassword($encoder->encodePassword($user, $jsonData->password));

        try {
            $entityManager->persist($user);
            $entityManager->flush();
        } catch (\Exception $exception) {
            return new Response(null, Response::HTTP_CONFLICT);
        }

        return new Response(json_encode(['id' => $user->getId()]), Response::HTTP_CREATED);
    }

    /**
     * @Route("/", methods={"GET"})
     * @Rest\QueryParam(name="order", requirements="(id|username)" allowBlank=false, default="id")
     * @Rest\QueryParam(name="offset", requirements="\d+" allowBlank=false, default="0")
     * @Rest\QueryParam(name="limit", requirements="\d+" allowBlank=false, default="25")
     * @param UserRepository $userRepository
     * @param ParamFetcher   $fetcher
     * @return Response
     */
    public function list(UserRepository $userRepository, ParamFetcher $fetcher): Response
    {
        $order  = $fetcher->get('order', true);
        $offset = (int)$fetcher->get('offset', true);
        $limit  = (int)$fetcher->get('limit', true);

        // todo: check validation, access rights, error handling

        $users = $userRepository->findAllOrderedPaginated($order, $offset, $limit);

        $list = [];
        foreach ($users as $user) {
            $list[] = [
                'id'       => $user->getId(),
                'username' => $user->getUsername()
            ];
        }

        return new Response(json_encode(['users' => $list]), Response::HTTP_OK);
    }

    /**
     * @Route("/{id<\d+>}", methods={"GET"})
     * @param User $user
     * @return JsonResponse
     */
    public function one(User $user): JsonResponse
    {
        // todo: implement logic

        return new JsonResponse([]);
    }

    /**
     * @Route("/{id<\d+>}", methods={"PUT"})
     * @param User                         $user
     * @param Request                      $request
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface       $entityManager
     * @return JsonResponse
     */
    public function update(User $user, Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $entityManager): Response
    {
        // todo: json validation, access rights, error handling

        $jsonData = json_decode($request->getContent());

        $user->setPassword($encoder->encodePassword($user, $jsonData->password));
        // todo: update role

        try {
            $entityManager->flush();
        } catch (\Exception $exception) {
            // todo: check error handling
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id<\d+>}", methods={"DELETE"})
     * @param User                   $user
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function delete(User $user, EntityManagerInterface $entityManager): Response
    {
        // todo: json validation, access rights, error handling

        try {
            $entityManager->remove($user);
            $entityManager->flush();
        } catch (\Exception $exception) {
            // todo: check error handling
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}