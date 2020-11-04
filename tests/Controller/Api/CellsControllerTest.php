<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CellsControllerTest extends WebTestCase
{
    public function testDelete()
    {
        $client = static::createClient();

        $client->xmlHttpRequest(
            'DELETE',
            '/api/cells/0/0'
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

        $client->xmlHttpRequest(
            'GET',
            '/api/cells/range/0/0/2/3'
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $responseJson = $client->getResponse()->getContent();

        $expectedJson = json_encode([
            'status' => 'OK',
            'data'   => [
                ["row" => 0, "col" => 0, "value" => 1],
                ["row" => 2, "col" => 3, "value" => 10]],
        ]);
        $this->assertJsonStringEqualsJsonString($expectedJson, $responseJson);
    }

    public function testOne()
    {
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
