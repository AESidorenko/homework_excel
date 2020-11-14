<?php

namespace App\Controller\Api\v1;

use App\Entity\Sheet;
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
    /**
     * @Route("/", methods={"POST"})
     * @param Request                $request
     * @param UserRepository         $userRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function create(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        // todo: json validation, access rights, error handling

        $jsonData = json_decode($request->getContent());
        $user     = $userRepository->findAll()[0]; // todo: use current logged user

        $sheet = (new Sheet())
            ->setName($jsonData->name)
            ->setOwner($user); // todo: resolve collision between User as entity and UserInterface object, set sheet owner

        $entityManager->persist($sheet);
        $entityManager->flush();

        return new Response(json_encode(['id' => $sheet->getId()]), Response::HTTP_CREATED, ['Content-type' => 'application/json']);
    }

    /**
     * @Route("/", methods={"GET"})
     * @Rest\QueryParam(name="offset", requirements="\d+", allowBlank=false, default="0")
     * @Rest\QueryParam(name="limit", requirements="\d+", allowBlank=false, default="25")
     * @param SheetRepository $sheetRepository
     * @param ParamFetcher    $fetcher
     * @return JsonResponse
     */
    public function list(SheetRepository $sheetRepository, ParamFetcher $fetcher): Response
    {
        $offset = (int)$fetcher->get('offset', true);
        $limit  = (int)$fetcher->get('limit', true);

        // todo: check validation, access rights, error handling

        $sheets = $sheetRepository->findAllPaginated($offset, $limit);

        $list = [];
        foreach ($sheets as $sheet) {
            $list[] = [
                'id'   => $sheet->getId(),
                'name' => $sheet->getUsername()
            ];
        }

        return new Response(json_encode(['sheets' => $list]), Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    /**
     * @Route("/{id<\d+>}", methods={"GET"})
     * @param Sheet $sheet
     * @return JsonResponse
     */
    public function one(Sheet $sheet): Response
    {
        $data = [
            'id'       => $sheet->getId(),
            'name'     => $sheet->getName(),
            'owner_id' => $sheet->getOwner()->getUsername()
        ];

        return new Response(json_encode($data), Response::HTTP_OK, ['Content-type' => 'application/json']);
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
        // todo: json validation, access rights, error handling

        $jsonData = json_decode($request->getContent());

        $sheet->setName($jsonData->name);

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
        // todo: access rights, error handling

        try {
            $entityManager->remove($sheet);
            $entityManager->flush();
        } catch (\Exception $exception) {
            // todo: check error handling
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id<\d+>}/dimensions", methods={"GET"})
     * @param Sheet          $sheet
     * @param CellRepository $cellRepository
     * @return JsonResponse
     */
    public function getDimensions(Sheet $sheet, CellRepository $cellRepository): Response
    {
        // todo: access rights, error handling
        $dimensions = $cellRepository->getDimensionsBySheet($sheet);

        $result = [
            "rows" => $dimensions['totalRows'],
            "cols" => $dimensions['totalCols']
        ];

        return new Response(json_encode($result), Response::HTTP_OK, ['Content-type' => 'application/json']);
    }
}