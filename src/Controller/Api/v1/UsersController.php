<?php

namespace App\Controller\Api\v1;

use App\Entity\User;
use App\Exception\JsonObjectValidationException;
use App\Helper\MissingArrayFieldsValidator;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @Route("/users")
 * @UniqueEntity("username")
 */
class UsersController extends AbstractController
{
    use MissingArrayFieldsValidator;

    /**
     * @Route("/", methods={"POST"}, condition="request.headers.get('Content-Type') === 'application/json'")
     * @ParamConverter("requestUser", converter="fos_rest.request_body")
     * @param User                             $requestUser
     * @param ConstraintViolationListInterface $validationErrors
     * @param Request                          $request
     * @param UserPasswordEncoderInterface     $encoder
     * @param EntityManagerInterface           $entityManager
     * @return JsonResponse
     */
    public function create(User $requestUser, ConstraintViolationListInterface $validationErrors, Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($validationErrors->count() > 0) {
            throw new JsonObjectValidationException($validationErrors);
        }

        $user = new User();
        $user->setUsername($requestUser->getUsername())
             ->setPassword($encoder->encodePassword($user, $requestUser->getPassword()));

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['id' => $user->getId()], Response::HTTP_CREATED);
    }

    /**
     * @Route("/", methods={"GET"})
     * @Rest\QueryParam(name="order", requirements="(id|username)", allowBlank=false, default="id")
     * @Rest\QueryParam(name="offset", requirements="\d+", allowBlank=false, default="0")
     * @Rest\QueryParam(name="limit", requirements="\d+", allowBlank=false, default="25")
     * @param UserRepository $userRepository
     * @param ParamFetcher   $fetcher
     * @return JsonResponse
     */
    public function list(UserRepository $userRepository, ParamFetcher $fetcher): JsonResponse
    {
        $order  = $fetcher->get('order', true);
        $offset = (int)$fetcher->get('offset', true);
        $limit  = (int)$fetcher->get('limit', true);

        $users = $userRepository->findAllOrderedPaginated($order, $offset, $limit);

        $list = [];
        foreach ($users as $user) {
            $list[] = [
                'id'       => $user->getId(),
                'username' => $user->getUsername()
            ];
        }

        return new JsonResponse(['users' => $list]);
    }

    /**
     * @Route("/{id<\d+>}", methods={"GET"})
     * @param User $user
     * @return JsonResponse
     */
    public function one(User $user): JsonResponse
    {
        $userData = [
            'username'     => $user->getUsername(),
            'sheets-count' => $user->getSheets()->count()
        ];

        return new JsonResponse(['user' => $userData]);
    }

    /**
     * @Route("/", methods={"PUT"}, condition="request.headers.get('Content-Type') === 'application/json'")
     * @ParamConverter("requestUser", converter="fos_rest.request_body")
     * @param User                             $requestUser
     * @param ConstraintViolationListInterface $validationErrors
     * @param UserRepository                   $userRepository
     * @param UserPasswordEncoderInterface     $encoder
     * @param EntityManagerInterface           $entityManager
     * @return Response
     */
    public function update(User $requestUser, ConstraintViolationListInterface $validationErrors, UserRepository $userRepository, UserPasswordEncoderInterface $encoder, EntityManagerInterface $entityManager): Response
    {
        if ($validationErrors->count() > 0) {
            throw new JsonObjectValidationException($validationErrors);
        }

        $user = $userRepository->findOneByUsername($requestUser->getUsername());
        $user->setPassword($encoder->encodePassword($user, $requestUser->getPassword()));

        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id<\d+>}", methods={"DELETE"})
     * @param User                   $user
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function delete(User $user, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}