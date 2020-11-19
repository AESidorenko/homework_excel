<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    public const USER_REFERENCE = "user_ref";

    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = (new User())
            ->setUsername('user1');

        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'password'
        ));

        $manager->persist($user);

        $manager->flush();

        $this->addReference(self::USER_REFERENCE, $user);
    }
}
