<?php

namespace App\Controller\Api\v1;

use App\Entity\User;
use App\Exception\JsonObjectValidationException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class AuthController extends AbstractController
{
    /**
     * @Route("/login", name="app_login", condition="request.headers.get('Content-Type') === 'application/json'")
     * @ParamConverter("requestUser", converter="fos_rest.request_body")
     * @param User                             $requestUser
     * @param ConstraintViolationListInterface $validationErrors
     * @param UserRepository                   $userRepository
     * @param UserPasswordEncoderInterface     $passwordEncoder
     * @param EntityManagerInterface           $entityManager
     * @return JsonResponse
     */
    public function login(
        User $requestUser,
        ConstraintViolationListInterface $validationErrors,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        if ($validationErrors->count() > 0) {
            throw new JsonObjectValidationException($validationErrors);
        }

        $user = $userRepository->findOneByUsername($requestUser->getUsername());
        if ($user === null) {
            throw new NotFoundHttpException('User not found');
        }

        if (!$passwordEncoder->isPasswordValid($user, $requestUser->getPassword())) {
            throw new UnauthorizedHttpException('Basic realm="Access to the staging API"', 'Invalid credentials');
        }

        $user->setApiToken(bin2hex(openssl_random_pseudo_bytes(16)));

        $entityManager->flush();

        return new JsonResponse(['token' => $user->getApiToken()]);
    }
}
