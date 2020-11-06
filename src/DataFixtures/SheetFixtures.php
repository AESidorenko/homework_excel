<?php

namespace App\DataFixtures;

use App\Entity\Sheet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SheetFixtures extends Fixture implements DependentFixtureInterface
{
    public const SHEET_REFERENCE = 'sheet_ref';

    public function load(ObjectManager $manager)
    {
        $sheet =
            (new Sheet())
                ->setName("test_sheet_1")
                ->setOwner($this->getReference(AppFixtures::USER_REFERENCE));

        $manager->persist($sheet);

        $manager->flush();

        $this->addReference(self::SHEET_REFERENCE, $sheet);
    }

    public function getDependencies()
    {
        return [
            AppFixtures::class,
        ];
    }
}
