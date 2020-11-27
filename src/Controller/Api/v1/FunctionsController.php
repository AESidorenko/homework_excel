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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sheets/{sheetId}/functions")
 * @ParamConverter("sheet", options={"id" = "sheetId"})
 */
class FunctionsController extends AbstractController
{
    /**
     * @Route("/sum")
     * @Rest\QueryParam(name="row", requirements="\d+", nullable=true)
     * @Rest\QueryParam(name="col", requirements="\d+", nullable=true)
     * @param Sheet          $sheet
     * @param CellRepository $cellRepository
     * @param ParamFetcher   $fetcher
     * @return JsonResponse
     */
    public function sum(Sheet $sheet, CellRepository $cellRepository, ParamFetcher $fetcher): JsonResponse
    {
        $row = $fetcher->get('row', false);
        $col = $fetcher->get('col', false);

        if ($row === null && $col === null) {
            throw new BadRequestHttpException('No dimension presented');
        }

        if ($row !== null && $col !== null) {
            throw new BadRequestHttpException('Single dimension required');
        }

        $rangeKind  = $row === null ? 'col' : 'row';
        $rangeIndex = (int)($rangeKind === 'col' ? $col : $row);

        $user   = $this->getUser();
        $result = $cellRepository->calculateSumByUserAndSheetAnd1dRange($user, $sheet, $rangeKind, $rangeIndex);

        return new JsonResponse(['result' => $result]);
    }

    /**
     * @Route("/average")
     * @Rest\QueryParam(name="row", requirements="\d+", nullable=true)
     * @Rest\QueryParam(name="col", requirements="\d+", nullable=true)
     * @param Sheet          $sheet
     * @param CellRepository $cellRepository
     * @param ParamFetcher   $fetcher
     * @return JsonResponse
     */
    public function average(Sheet $sheet, CellRepository $cellRepository, ParamFetcher $fetcher): Response
    {
        $row = $fetcher->get('row', false);
        $col = $fetcher->get('col', false);

        if ($row === null && $col === null) {
            throw new BadRequestHttpException('No dimension presented');
        }

        if ($row !== null && $col !== null) {
            throw new BadRequestHttpException('Dimension is not single');
        }

        $rangeKind  = $row === null ? 'col' : 'row';
        $rangeIndex = (int)($rangeKind === 'col' ? $col : $row);

        $user   = $this->getUser();
        $result = $cellRepository->calculateAverageByUserAndSheetAnd1dRange($user, $sheet, $rangeKind, $rangeIndex);

        return new JsonResponse(['result' => $result]);
    }

    /**
     * @Route("/percentile")
     * @Rest\QueryParam(name="row", requirements="\d+", nullable=true)
     * @Rest\QueryParam(name="col", requirements="\d+", nullable=true)
     * @Rest\QueryParam(name="parameter", requirements="\d*\.?\d+", allowBlank=false)
     * @param Sheet          $sheet
     * @param CellRepository $cellRepository
     * @param ParamFetcher   $fetcher
     * @return JsonResponse
     */
    public function percentile(Sheet $sheet, CellRepository $cellRepository, ParamFetcher $fetcher): Response
    {
        $row       = $fetcher->get('row', true);
        $col       = $fetcher->get('col', true);
        $parameter = (float)$fetcher->get('parameter', true);

        if ($row === null && $col === null) {
            throw new BadRequestHttpException('No dimension presented');
        }

        if ($row !== null && $col !== null) {
            throw new BadRequestHttpException('Dimension is not single');
        }

        $rangeKind  = $row === null ? 'col' : 'row';
        $rangeIndex = (int)($rangeKind === 'col' ? $col : $row);

        $result = $cellRepository->calculatePercentileByUserAndSheetAnd1dRangeAndParameter($user, $sheet, $rangeKind, $rangeIndex, $parameter);

        return new JsonResponse(['result' => $result]);
    }
}
