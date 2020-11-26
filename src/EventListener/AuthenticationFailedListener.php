<?php

namespace App\EventListener;

use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;

class AuthenticationFailedListener
{
    public function onSecurityAuthenticationFailureEvent1(AuthenticationFailureEvent $event)
    {
        var_dump($event);
    }
}