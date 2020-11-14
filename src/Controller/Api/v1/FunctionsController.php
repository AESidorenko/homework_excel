<?php

namespace App\Controller\Api\v1;

use App\Entity\Sheet;
use App\Repository\CellRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sheets/{sheetId}/functions")
 * @ParamConverter("sheet", options={"id" = "sheetId"})
 */
class FunctionsController extends AbstractController
{
    /**
     * @Route("/sum")
     * @Rest\QueryParam(name="row", requirements="\d+", default=null)
     * @Rest\QueryParam(name="col", requirements="\d+", default=null)
     * @param Sheet          $sheet
     * @param CellRepository $cellRepository
     * @param ParamFetcher   $fetcher
     * @return JsonResponse
     */
    public function sum(Sheet $sheet, CellRepository $cellRepository, ParamFetcher $fetcher): Response
    {
        // todo: params validation, access rights, error handling
        $row = $fetcher->get('row', false);
        $col = $fetcher->get('col', false);

        if (($row === null && $col === null) || ($row !== null && $col !== null)) {
            // todo: handle error
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        $rangeKind  = $row === null ? 'col' : 'row';
        $rangeIndex = (int)($rangeKind === 'col' ? $col : $row);

        $result = $cellRepository->calculateSumBySheetAnd1dRange($sheet, $rangeKind, $rangeIndex);

        return new Response(json_encode(['result' => $result]), Response::HTTP_OK);
    }

    /**
     * @Route("/average")
     * @Rest\QueryParam(name="row", requirements="\d+", default=null)
     * @Rest\QueryParam(name="col", requirements="\d+", default=null)
     * @param Sheet          $sheet
     * @param CellRepository $cellRepository
     * @param ParamFetcher   $fetcher
     * @return JsonResponse
     */
    public function average(Sheet $sheet, CellRepository $cellRepository, ParamFetcher $fetcher): Response
    {
        // todo: params validation, access rights, error handling
        $row = $fetcher->get('row', false);
        $col = $fetcher->get('col', false);

        if (($row === null && $col === null) || ($row !== null && $col !== null)) {
            // todo: handle error
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        $rangeKind  = $row === null ? 'col' : 'row';
        $rangeIndex = (int)($rangeKind === 'col' ? $col : $row);

        $result = $cellRepository->calculateAverageBySheetAnd1dRange($sheet, $rangeKind, $rangeIndex);

        return new Response(json_encode(['result' => $result]), Response::HTTP_OK);
    }

    /**
     * @Route("/percentile")
     * @Rest\QueryParam(name="row", requirements="\d+", default=null)
     * @Rest\QueryParam(name="col", requirements="\d+", default=null)
     * @Rest\QueryParam(name="parameter", requirements="\d*\.?\d+", allowBlank=false)
     * @param Sheet          $sheet
     * @param CellRepository $cellRepository
     * @param ParamFetcher   $fetcher
     * @return JsonResponse
     */
    public function percentile(Sheet $sheet, CellRepository $cellRepository, ParamFetcher $fetcher): Response
    {
        // todo: params validation, access rights, error handling
        $row       = $fetcher->get('row', false);
        $col       = $fetcher->get('col', false);
        $parameter = (float)$fetcher->get('parameter', true);

        if (($row === null && $col === null) || ($row !== null && $col !== null)) {
            // todo: handle error
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        $rangeKind  = $row === null ? 'col' : 'row';
        $rangeIndex = (int)($rangeKind === 'col' ? $col : $row);

        $result = $cellRepository->calculatePercentileBySheetAnd1dRangeAndParameter($sheet, $rangeKind, $rangeIndex, $parameter);

        return new Response(json_encode(['result' => $result]), Response::HTTP_OK);
    }
}
