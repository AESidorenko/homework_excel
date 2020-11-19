<?php

namespace App\Controller\Api\v1;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     * @param Request                      $request
     * @param UserRepository               $userRepository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param EntityManagerInterface       $entityManager
     * @return Response
     */
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager
    ): Response
    {
        $jsonData = json_decode($request->getContent(), true);
        if ($jsonData === null) {
            return new Response(json_encode([]), Response::HTTP_BAD_REQUEST);  // todo: handle error
        }

        if (!array_key_exists('username', $jsonData) || !array_key_exists('password', $jsonData)) {
            return new Response(json_encode([]), Response::HTTP_BAD_REQUEST);  // todo: handle error
        }

        $user = $userRepository->findOneByUsername($jsonData['username']);
        if ($user === null) {
            return new Response(json_encode([]), Response::HTTP_NOT_FOUND);  // todo: handle error
        }

        if (!$passwordEncoder->isPasswordValid($user, $jsonData['password'])) {
            return new Response(json_encode([]), Response::HTTP_UNAUTHORIZED);  // todo: handle error
        }

        $user->SetApiToken(bin2hex(openssl_random_pseudo_bytes(16)));

        $entityManager->flush();

        $responseData = [
            'token' => $user->getApiToken()
        ];

        return new Response(json_encode($responseData));
    }
}
