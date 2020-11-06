<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public const USER_REFERENCE = "user_ref";

    public function load(ObjectManager $manager)
    {
        $user =
            (new User())
                ->setUsername("user1")
                ->setPassword("pass");

        $manager->persist($user);

        $manager->flush();

        $this->addReference(self::USER_REFERENCE, $user);
    }
}
