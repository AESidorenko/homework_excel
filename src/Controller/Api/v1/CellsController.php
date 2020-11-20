<?php

namespace App\Controller\Api\v1;

use App\Entity\Cell;
use App\Entity\Sheet;
use App\Helper\MissingArrayFieldsValidator;
use App\Repository\CellRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sheets/{sheetId}/cells")
 * @ParamConverter("sheet", options={"id" = "sheetId"})
 */
class CellsController extends AbstractController
{
    use MissingArrayFieldsValidator;

    /**
     * @Route("/", methods={"GET"})
     * @Rest\QueryParam(name="left", requirements="\d+", allowBlank=false)
     * @Rest\QueryParam(name="top", requirements="\d+", allowBlank=false)
     * @Rest\QueryParam(name="right", requirements="\d+", allowBlank=false)
     * @Rest\QueryParam(name="bottom", requirements="\d+", allowBlank=false)
     * @param Sheet          $sheet
     * @param ParamFetcher   $fetcher
     * @param CellRepository $cellRepository
     * @return JsonResponse
     */
    public function range(Sheet $sheet, ParamFetcher $fetcher, CellRepository $cellRepository): JsonResponse
    {
        $left   = $fetcher->get('left', true);
        $top    = $fetcher->get('top', true);
        $right  = $fetcher->get('right', true);
        $bottom = $fetcher->get('bottom', true);

        /** @var Cell[] $cells */
        $cells = $cellRepository->findAllBySheetAndRange($sheet, $left, $top, $right, $bottom);

        $data = [];
        foreach ($cells as $cell) {
            $data[] = [
                'row'   => $cell->getRow(),
                'col'   => $cell->getCol(),
                'value' => $cell->getValue(),
            ];
        }

        return new JsonResponse(['cells' => $data]);
    }

    /**
     * @Route("/", methods={"PUT"})
     * @Rest\QueryParam(name="row", requirements="\d+", allowBlank=false)
     * @Rest\QueryParam(name="col", requirements="\d+", allowBlank=false)
     * @param Sheet                  $sheet
     * @param ParamFetcher           $fetcher
     * @param EntityManagerInterface $entityManager
     * @param Request                $request
     * @param CellRepository         $cellRepository
     * @return JsonResponse
     */
    public function update(Sheet $sheet, ParamFetcher $fetcher, EntityManagerInterface $entityManager, Request $request, CellRepository $cellRepository): Response
    {
        $row = $fetcher->get('row', true);
        $col = $fetcher->get('col', true);

        $jsonData = json_decode($request->getContent(), true);
        $this->AssertSchema($jsonData, ['value']);

        /** @var Cell[] $cells */
        $cell = $cellRepository->findOneBySheetAndCoordinates($sheet, $row, $col);
        if ($cell === null) {
            $cell = new Cell();
            $cell
                ->setSheet($sheet)
                ->setRow($row)
                ->setCol($col)
                ->setValue($jsonData['value']);

            $entityManager->persist($cell);
        } else {
            $cell->setValue($jsonData['value']);
        }

        $entityManager->flush();

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/", methods={"DELETE"})
     * @Rest\QueryParam(name="row", requirements="\d+", nullable=false)
     * @Rest\QueryParam(name="col", requirements="\d+", nullable=false)
     * @param Sheet                  $sheet
     * @param ParamFetcher           $fetcher
     * @param CellRepository         $cellRepository
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function delete(Sheet $sheet, ParamFetcher $fetcher, CellRepository $cellRepository, EntityManagerInterface $entityManager): Response
    {
        $row = $fetcher->get('row', true);
        $col = $fetcher->get('col', true);

        $cell = $cellRepository->findOneBySheetAndCoordinates($sheet, $row, $col);
        if ($cell === null) {
            throw new NotFoundHttpException('Cell not found');
        }

        $entityManager->remove($cell);
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}