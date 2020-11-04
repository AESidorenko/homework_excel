<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/cells")
 */
class CellsController extends AbstractController
{
    /**
     * @Route("/range/{leftRow<\d+>}/{topCol<\d+>}/{bottomRow<\d+>}/{rightCol<\d+>}", methods={"GET"})
     * @return JsonResponse
     */
    public function range(int $leftRow, int $topCol, int $bottomRow, int $rightCol): JsonResponse
    {
        return new JsonResponse([
            'status' => 'OK',
            'data'   => [
                ["row" => 0, "col" => 0, "value" => 1],
                ["row" => 2, "col" => 3, "value" => 10]],
        ]);
    }

    /**
     * @Route("/{row<\d+>}/{col<\d+>}", methods={"GET"})
     * @param int $row
     * @param int $col
     * @return JsonResponse
     */
    public function one(int $row, int $col): JsonResponse
    {
        return new JsonResponse([
            'status' => 'OK',
            'data'   => [
                "row"   => 2,
                "col"   => 3,
                "value" => 10
            ],
        ]);
    }

    /**
     * @Route("/{row<\d+>}/{col<\d+>}", methods={"PATCH"})
     * @param int $row
     * @param int $col
     * @return JsonResponse
     */
    public function update(int $row, int $col): JsonResponse
    {
        return new JsonResponse([]);
    }

    /**
     * @Route("/{row<\d+>}/{col<\d+>}", methods={"DELETE"})
     * @param int $row
     * @param int $col
     * @return JsonResponse
     */
    public function delete(int $row, int $col): JsonResponse
    {
        return new JsonResponse([
            'status'  => 'OK',
            'message' => 'Cell data deleted',
        ]);
    }
}