<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/operations")
 */
class OperationsController extends AbstractController
{
    /**
     * @Route("/sum/{target}/{id<\d+>}")
     * @param string $target
     * @param int    $id
     * @return JsonResponse
     */
    public function sum(string $target, int $id): JsonResponse
    {
        return new JsonResponse([]);
    }

    /**
     * @Route("/average/{target}/{id<\d+>}")
     * @param string $target
     * @param int    $id
     * @return JsonResponse
     */
    public function average(string $target, int $id): JsonResponse
    {
        return new JsonResponse([]);
    }

    /**
     * @Route("/percentile/{target}/{id<\d+>}/{parameter}")
     * @param string $target
     * @param int    $id
     * @param float  $parameter
     * @return JsonResponse
     */
    public function percentile(string $target, int $id, float $parameter): JsonResponse
    {
        return new JsonResponse([]);
    }
}
