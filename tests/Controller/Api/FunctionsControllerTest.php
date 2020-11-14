<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class FunctionsControllerTest extends WebTestCase
{

    public function testAverage()
    {

    }

    public function testPercentile()
    {

    }

    public function testSum()
    {
        $client = static::createClient();
        $client->followRedirects(true);
        $client->xmlHttpRequest(
            'GET',
            '/api/v1/sheets/1/functions/average',
            [
                "row"       => 0,
                "parameter" => 0.95
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseHeaderSame('Content-type', 'application/json');

        $responseJson = $client->getResponse()->getContent();

        $expectedJson = ["result" => 5];

        $this->assertJsonStringEqualsJsonString(json_encode($expectedJson), $responseJson);
    }
}
