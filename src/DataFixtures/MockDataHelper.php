<?php

namespace App\DataFixtures;

class MockDataHelper
{
    public static function generate2dFilledCellArray(int $left, int $top, int $bottom, int $right)
    {
        for ($row = $top; $row <= $bottom; $row++) {
            for ($col = $left; $col <= $right; $col++) {
                yield [
                    "row"   => $row,
                    "col"   => $col,
                    "value" => $row + $col
                ];
            }
        }
    }
}