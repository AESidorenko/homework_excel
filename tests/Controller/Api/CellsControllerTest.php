<?php

namespace App\Tests\Controller\Api;

use App\DataFixtures\MockDataHelper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CellsControllerTest extends WebTestCase
{
    public function testDelete()
    {
        $this->markTestSkipped();

        return;

        $client = static::createClient();

        $client->xmlHttpRequest(
            'DELETE',
            '/api/v1/sheets/1/cells/0/0'
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $responseJson = $client->getResponse()->getContent();

        $expectedJson = json_encode([
            'status'  => 'OK',
            'message' => 'Cell data deleted',
        ]);
        $this->assertJsonStringEqualsJsonString($expectedJson, $responseJson);
    }

    public function testRange()
    {
        $client = static::createClient();
        $client->followRedirects(true);
        $client->xmlHttpRequest(
            'GET',
            '/api/v1/sheets/1/cells',
            [
                "left"   => 0,
                "top"    => 0,
                "bottom" => 4,
                "right"  => 5
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $responseJson = $client->getResponse()->getContent();

        $expectedJson = ["cells" => []];
        foreach (MockDataHelper::generate2dFilledCellArray(0, 0, 4, 5) as $item) {
            $expectedJson["cells"][] = $item;
        }

        $this->assertJsonStringEqualsJsonString(json_encode($expectedJson), $responseJson);
    }

    public function testOne()
    {
        $this->markTestSkipped();

        return;

        $client = static::createClient();

        $client->xmlHttpRequest(
            'GET',
            '/api/cells/2/3'
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $responseJson = $client->getResponse()->getContent();

        $expectedJson = json_encode([
            'status' => 'OK',
            'data'   => [
                "row"   => 2,
                "col"   => 3,
                "value" => 10
            ],
        ]);
        $this->assertJsonStringEqualsJsonString($expectedJson, $responseJson);
    }

    public function testUpdate()
    {
    }
}
