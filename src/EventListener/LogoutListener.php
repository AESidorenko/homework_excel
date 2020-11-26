<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutListener
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * AuthListener constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onSymfonyComponentSecurityHttpEventLogoutEvent(LogoutEvent $event)
    {
        /** @var User $user */
        $user = $event->getToken()->getUser();

        $user->setApiToken(null);

        $this->em->flush();

        $event->setResponse(new Response(null, Response::HTTP_NO_CONTENT));
    }
}