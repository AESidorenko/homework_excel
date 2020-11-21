<?php

namespace App\Controller\Api\v1;

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
 * @Route("/sheets")
 */
class SheetsController extends AbstractController
{
    use MissingArrayFieldsValidator;

    /**
     * @Route("/", methods={"POST"}, condition="request.headers.get('Content-Type') === 'application/json'")
     * @ParamConverter("requestSheet", converter="fos_rest.request_body")
     * @param Sheet                            $requestSheet
     * @param ConstraintViolationListInterface $validationErrors
     * @param EntityManagerInterface           $entityManager
     * @return JsonResponse
     */
    public function create(Sheet $requestSheet, ConstraintViolationListInterface $validationErrors, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($validationErrors->count() > 0) {
            throw new JsonObjectValidationException($validationErrors);
        }

        $user = $this->getUser();

        $sheet = (new Sheet())
            ->setName($requestSheet->getName())
            ->setOwner($user);

        $entityManager->persist($sheet);
        $entityManager->flush();

        return new JsonResponse(['id' => $sheet->getId()], Response::HTTP_CREATED);
    }

    /**
     * @Route("/", methods={"GET"})
     * @Rest\QueryParam(name="offset", requirements="\d+", allowBlank=false, default="0")
     * @Rest\QueryParam(name="limit", requirements="\d+", allowBlank=false, default="25")
     * @param SheetRepository $sheetRepository
     * @param ParamFetcher    $fetcher
     * @return JsonResponse
     */
    public function list(SheetRepository $sheetRepository, ParamFetcher $fetcher): JsonResponse
    {
        $offset = (int)$fetcher->get('offset', true);
        $limit  = (int)$fetcher->get('limit', true);

        $user        = $this->getUser();
        $sheets      = $sheetRepository->findAllPaginatedByUserAndOffsetAndLimit($user, $offset, $limit);
        $totalSheets = $sheetRepository->countByUser($user);

        $list = [];
        foreach ($sheets as $sheet) {
            $list[] = [
                'id'         => $sheet->getId(),
                'name'       => $sheet->getName(),
                'owner_name' => $sheet->getOwner()->getUsername()
            ];
        }

        return new JsonResponse(['total_sheets' => $totalSheets, 'sheets' => $list]);
    }

    /**
     * @Route("/{id<\d+>}", methods={"GET"})
     * @param Sheet $sheet
     * @return JsonResponse
     */
    public function one(Sheet $sheet): JsonResponse
    {
        $data = [
            'name'     => $sheet->getName(),
            'owner_id' => $sheet->getOwner()->getUsername()
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/{id<\d+>}", methods={"PUT"}, condition="request.headers.get('Content-Type') === 'application/json'")
     * @ParamConverter("requestSheet", converter="fos_rest.request_body")
     * @param Sheet                            $requestSheet
     * @param ConstraintViolationListInterface $validationErrors
     * @param SheetRepository                  $sheetRepository
     * @param EntityManagerInterface           $entityManager
     * @return JsonResponse
     */
    public function update(Sheet $requestSheet, ConstraintViolationListInterface $validationErrors, SheetRepository $sheetRepository, EntityManagerInterface $entityManager): Response
    {
        if ($validationErrors->count() > 0) {
            throw new JsonObjectValidationException($validationErrors);
        }

        $user  = $this->getUser();
        $sheet = $sheetRepository->findOneByUserAndName($user, $requestSheet->getName());
        if ($sheet === null) {
            throw new NotFoundHttpException("Sheet not found");
        }

        $sheet->setName($requestSheet->getName());

        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id<\d+>}", methods={"DELETE"})
     * @param Sheet                  $sheet
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function delete(Sheet $sheet, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($sheet);
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id<\d+>}/dimensions", methods={"GET"})
     * @param Sheet          $sheet
     * @param CellRepository $cellRepository
     * @return JsonResponse
     */
    public function getDimensions(Sheet $sheet, CellRepository $cellRepository): JsonResponse
    {
        $user       = $this->getUser();
        $dimensions = $cellRepository->getDimensionsByUserAndSheet($user, $sheet);

        $result = [
            "rows" => $dimensions['totalRows'],
            "cols" => $dimensions['totalCols']
        ];

        return new JsonResponse($result);
    }
}