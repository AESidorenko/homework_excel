<?php

namespace App\Controller\Api\v1;

use App\Helper\MissingArrayFieldsValidator;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthController extends AbstractController
{
    use MissingArrayFieldsValidator;

    /**
     * @Route("/login", name="app_login", condition="request.headers.get('Content-Type') === 'application/json'")
     * @param Request                      $request
     * @param UserRepository               $userRepository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param EntityManagerInterface       $entityManager
     * @return JsonResponse
     */
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $jsonData = json_decode($request->getContent(), true);
        $this->AssertSchema($jsonData, ['username', 'password']);

        $user = $userRepository->findOneByUsername($jsonData['username']);
        if ($user === null) {
            throw new NotFoundHttpException('User not found');
        }

        if (!$passwordEncoder->isPasswordValid($user, $jsonData['password'])) {
            throw new UnauthorizedHttpException('Basic realm="Access to the staging API"', 'Invalid credentials');
        }

        $user->SetApiToken(bin2hex(openssl_random_pseudo_bytes(16)));

        $entityManager->flush();

        return new JsonResponse(['token' => $user->getApiToken()]);
    }
}
