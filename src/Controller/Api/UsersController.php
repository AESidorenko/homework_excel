<?php

namespace App\Controller\Api;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/users")
 */
class UsersController extends AbstractController
{
    /**
     * @Route("/", methods={"POST"})
     */
    public function create(): JsonResponse
    {
        return new JsonResponse([]);
    }

    /**
     * @Route("/", methods={"GET"})
     */
    public function list(): JsonResponse
    {
        return new JsonResponse([]);
    }

    /**
     * @Route("/{id<\d+>}", methods={"GET"})
     * @param User $user
     * @return JsonResponse
     */
    public function one(User $user): JsonResponse
    {
        return new JsonResponse([]);
    }

    /**
     * @Route("/{id<\d+>}", methods={"PATCH"})
     * @param User $user
     * @return JsonResponse
     */
    public function update(User $user): JsonResponse
    {
        return new JsonResponse([]);
    }

    /**
     * @Route("/{id<\d+>}", methods={"DELETE"})
     * @param User $user
     * @return JsonResponse
     */
    public function delete(User $user): JsonResponse
    {
        return new JsonResponse([]);
    }
}