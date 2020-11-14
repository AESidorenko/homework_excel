<?php

namespace App\Controller\Api\v1;

use App\Entity\Cell;
use App\Entity\Sheet;
use App\Repository\CellRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sheets/{sheetId}/cells")
 * @ParamConverter("sheet", options={"id" = "sheetId"})
 */
class CellsController extends AbstractController
{
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
    public function range(Sheet $sheet, ParamFetcher $fetcher, CellRepository $cellRepository): Response
    {
        // todo: params validation, access rights, error handling

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

        return new Response(json_encode(['cells' => $data]), Response::HTTP_OK);
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
        // todo: json validation, access rights, error handling

        $jsonData = json_decode($request->getContent());

        $row = $fetcher->get('row', true);
        $col = $fetcher->get('col', true);

        /** @var Cell[] $cells */
        $cell = $cellRepository->findOneBySheetAndCoordinates($sheet, $row, $col);
        if ($cell === null) {
            $cell = new Cell();
            $cell
                ->setRow($row)
                ->setCol($col)
                ->setValue($jsonData->value);

            $entityManager->persist($cell);
        } else {
            $cell->setValue($jsonData->value);
        }

        try {
            $entityManager->flush();
        } catch (\Exception $exception) {
            // todo: handle error
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{row<\d+>}/{col<\d+>}", methods={"DELETE"})
     * @param Sheet                  $sheet
     * @param int                    $row
     * @param int                    $col
     * @param ParamFetcher           $fetcher
     * @param CellRepository         $cellRepository
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function delete(Sheet $sheet, int $row, int $col, ParamFetcher $fetcher, CellRepository $cellRepository, EntityManagerInterface $entityManager): Response
    {
        // todo: params validation, access rights, error handling

        $row = $fetcher->get('row', true);
        $col = $fetcher->get('col', true);

        $cell = $cellRepository->findOneBySheetAndCoordinates($sheet, $row, $col);
        if ($cell === null) {
            // todo: handle errors
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        try {
            $entityManager->remove($sheet);
            $entityManager->flush();
        } catch (\Exception $exception) {
            // todo: handle error
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        return new Response(json_encode([
            'status'  => 'OK',
            'message' => 'Cell data deleted',
        ]), Response::HTTP_OK);
    }
}