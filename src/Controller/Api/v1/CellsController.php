<?php

namespace App\Controller\Api\v1;

use App\Repository\CellRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sheet/{sheetId}/cells")
 */
class CellsController extends AbstractController
{
    /**
     * @Route("/", methods={"GET"})
     * @Rest\QueryParam(name="left", requirements="\d+", allowBlank=false)
     * @Rest\QueryParam(name="top", requirements="\d+", allowBlank=false)
     * @Rest\QueryParam(name="right", requirements="\d+", allowBlank=false)
     * @Rest\QueryParam(name="bottom", requirements="\d+", allowBlank=false)
     * @param int $sheetId
     * @return JsonResponse
     */
    public function range(int $sheetId, ParamFetcher $fetcher, CellRepository $cellRepository): JsonResponse
    {
        $left   = $fetcher->get('left', true);
        $top    = $fetcher->get('top', true);
        $right  = $fetcher->get('right', true);
        $bottom = $fetcher->get('bottom', true);

        $cells = $cellRepository->findAllInRange($left, $top, $right, $bottom);

        return new JsonResponse([
            'cells' => [
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