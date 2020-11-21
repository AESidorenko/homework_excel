<?php

namespace App\Controller\Api\v1;

use App\Entity\Cell;
use App\Entity\Sheet;
use App\Exception\JsonObjectValidationException;
use App\Helper\MissingArrayFieldsValidator;
use App\Repository\CellRepository;
use App\Repository\SheetRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @Route("/sheets/{sheetId}/cells")
 * @ParamConverter("sheet", options={"id" = "sheetId"})
 */
class CellsController extends AbstractController
{
    use MissingArrayFieldsValidator;

    /**
     * @Route("/", methods={"GET"})
     * @Rest\QueryParam(name="left", requirements="\d+", nullable=false, strict=true)
     * @Rest\QueryParam(name="top", requirements="\d+", nullable=false, strict=true)
     * @Rest\QueryParam(name="right", requirements="\d+", nullable=false, strict=true)
     * @Rest\QueryParam(name="bottom", requirements="\d+", nullable=false, strict=true)
     * @param Sheet           $sheet
     * @param ParamFetcher    $fetcher
     * @param CellRepository  $cellRepository
     * @param SheetRepository $sheetRepository
     * @return JsonResponse
     */
    public function range(Sheet $sheet, ParamFetcher $fetcher, CellRepository $cellRepository, SheetRepository $sheetRepository): JsonResponse
    {
        $left   = $fetcher->get('left');
        $right  = $fetcher->get('right');
        $bottom = $fetcher->get('bottom');
        $top    = $fetcher->get('top');

        $user = $this->getUser();

        /** @var Cell[] $cells */
        $cells = $cellRepository->findAllByUserAndSheetAndRange($user, $sheet, $left, $top, $right, $bottom);

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
     * @Route("/", methods={"PUT"}, condition="request.headers.get('Content-Type') === 'application/json'")
     * @Rest\QueryParam(name="row", requirements="\d+", nullable=false, strict=true)
     * @Rest\QueryParam(name="col", requirements="\d+", nullable=false, strict=true)
     * @ParamConverter("requestCell", converter="fos_rest.request_body")
     * @param Sheet                            $sheet
     * @param Cell                             $requestCell
     * @param ConstraintViolationListInterface $validationErrors
     * @param ParamFetcher                     $fetcher
     * @param EntityManagerInterface           $entityManager
     * @param CellRepository                   $cellRepository
     * @return JsonResponse
     */
    public function update(Sheet $sheet, Cell $requestCell, ConstraintViolationListInterface $validationErrors, ParamFetcher $fetcher, EntityManagerInterface $entityManager, CellRepository $cellRepository): Response
    {
        $row = $fetcher->get('row');
        $col = $fetcher->get('col');

        if ($validationErrors->count() > 0) {
            throw new JsonObjectValidationException($validationErrors);
        }

        $user = $this->getUser();
        $cell = $cellRepository->findOneByUserAndSheetAndCoordinates($user, $sheet, $row, $col) ?? new Cell();

        $cell
            ->setSheet($sheet)
            ->setRow($row)
            ->setCol($col)
            ->setValue($requestCell->getValue());

        $entityManager->persist($cell);
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

        $user = $this->getUser();
        $cell = $cellRepository->findOneByUserAndSheetAndCoordinates($user, $sheet, $row, $col);
        if ($cell === null) {
            throw new NotFoundHttpException('Cell not found');
        }

        $entityManager->remove($cell);
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}