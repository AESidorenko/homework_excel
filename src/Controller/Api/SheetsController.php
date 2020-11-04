<?php

namespace App\Controller\Api;

use App\Entity\Sheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sheets")
 */
class SheetsController extends AbstractController
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
     * @param Sheet $sheet
     * @return JsonResponse
     */
    public function one(Sheet $sheet): JsonResponse
    {
        $sheetOwner = $sheet->getOwner();

        return new JsonResponse([]);
    }

    /**
     * @Route("/{id<\d+>}", methods={"PATCH"})
     * @param Sheet $sheet
     * @return JsonResponse
     */
    public function update(Sheet $sheet): JsonResponse
    {
        return new JsonResponse([]);
    }

    /**
     * @Route("/{id<\d+>}", methods={"DELETE"})
     * @param Sheet $sheet
     * @return JsonResponse
     */
    public function delete(Sheet $sheet): JsonResponse
    {
        return new JsonResponse([]);
    }
}