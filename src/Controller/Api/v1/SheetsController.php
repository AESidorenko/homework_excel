<?php

namespace App\Controller\Api\v1;

use App\Entity\Sheet;
use App\Helper\MissingArrayFieldsValidator;
use App\Repository\CellRepository;
use App\Repository\SheetRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sheets")
 */
class SheetsController extends AbstractController
{
    use MissingArrayFieldsValidator;

    /**
     * @Route("/", methods={"POST"})
     * @param Request                $request
     * @param UserRepository         $userRepository
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function create(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $jsonData = json_decode($request->getContent(), true);
        $this->AssertSchema($jsonData, ['name']);

        $user = $this->getUser();

        $sheet = (new Sheet())
            ->setName($jsonData['name'])
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

        $sheets = $sheetRepository->findAllPaginated($offset, $limit);

        $list = [];
        foreach ($sheets as $sheet) {
            $list[] = [
                'id'         => $sheet->getId(),
                'name'       => $sheet->getName(),
                'owner_name' => $sheet->getOwner()->getUsername()
            ];
        }

        return new JsonResponse(['sheets' => $list]);
    }

    /**
     * @Route("/{id<\d+>}", methods={"GET"})
     * @param Sheet $sheet
     * @return JsonResponse
     */
    public function one(Sheet $sheet): JsonResponse
    {
        $data = [
            'id'       => $sheet->getId(),
            'name'     => $sheet->getName(),
            'owner_id' => $sheet->getOwner()->getUsername()
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/{id<\d+>}", methods={"PUT"})
     * @param Sheet                  $sheet
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function update(Sheet $sheet, Request $request, EntityManagerInterface $entityManager): Response
    {
        $jsonData = json_decode($request->getContent(), true);
        $this->AssertSchema($jsonData, 'name');

        $sheet->setName($jsonData['name']);

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
        $dimensions = $cellRepository->getDimensionsBySheet($sheet);

        $result = [
            "rows" => $dimensions['totalRows'],
            "cols" => $dimensions['totalCols']
        ];

        return new JsonResponse($result);
    }
}