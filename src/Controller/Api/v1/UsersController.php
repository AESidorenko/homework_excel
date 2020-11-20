<?php

namespace App\Controller\Api\v1;

use App\Entity\User;
use App\Helper\MissingArrayFieldsValidator;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/users")
 */
class UsersController extends AbstractController
{
    use MissingArrayFieldsValidator;

    /**
     * @Route("/", methods={"POST"})
     * @param Request                      $request
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface       $entityManager
     * @return JsonResponse
     */
    public function create(Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $entityManager): JsonResponse
    {
        $jsonData = json_decode($request->getContent(), true);
        $this->AssertSchema($jsonData, ['username', 'password']);

        $user = new User();
        $user->setUsername($jsonData['username'])
             ->setPassword($encoder->encodePassword($user, $jsonData['password']));

        try {
            $entityManager->persist($user);
            $entityManager->flush();
        } catch (\Exception $exception) {
            throw new ConflictHttpException();
        }

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
     * @Route("/{id<\d+>}", methods={"PUT"})
     * @param User                         $user
     * @param Request                      $request
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface       $entityManager
     * @return Response
     */
    public function update(User $user, Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $entityManager): Response
    {
        $jsonData = json_decode($request->getContent(), true);
        $this->AssertSchema($jsonData, ['password']);

        $user->setPassword($encoder->encodePassword($user, $jsonData['password']));

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