<?php

namespace App\DataFixtures;

use App\Entity\Cell;
use App\Entity\Sheet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CellFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var Sheet $sheet */
        $sheet = $this->getReference(SheetFixtures::SHEET_REFERENCE);

        foreach (MockDataHelper::generate2dFilledCellArray(0, 0, 4, 5) as $item) {
            $cell =
                (new Cell())
                    ->setSheet($sheet)
                    ->setRow($item["row"])
                    ->setCol($item["col"])
                    ->setValue($item["value"]);

            $manager->persist($cell);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            SheetFixtures::class,
        ];
    }
}
