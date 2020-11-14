<?php

namespace App\Tests\Controller\Api\v1;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SheetsControllerTest extends WebTestCase
{

    public function testOne()
    {
        $client = static::createClient();
        $client->followRedirects(true);
        $client->xmlHttpRequest(
            'GET',
            '/api/v1/sheets/1'
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseHeaderSame('Content-type', 'application/json');

        $responseJson = $client->getResponse()->getContent();

        $expectedJson = [
            "id"       => 1,
            "name"     => "test_sheet_1",
            "owner_id" => "user1"
        ];

        $this->assertJsonStringEqualsJsonString(json_encode($expectedJson), $responseJson);
    }

    public function testUpdate()
    {
        $client = static::createClient();
        $client->followRedirects(true);
        $client->xmlHttpRequest(
            'PUT',
            '/api/v1/sheets/1',
            [],
            [],
            [],
            json_encode(['name' => 'test_sheet_1_updated'])
        );

        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
    }

    public function testCreate()
    {
        $client = static::createClient();
        $client->followRedirects(true);
        $client->xmlHttpRequest(
            'POST',
            '/api/v1/sheets/',
            [],
            [],
            [],
            json_encode(['name' => 'test_sheet_2'])
        );

        $responseJson = $client->getResponse()->getContent();

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $expectedJson = json_encode(['id' => 2]);
        $this->assertJsonStringEqualsJsonString($expectedJson, $responseJson);
    }

    public function testList()
    {

    }

    public function testDelete()
    {

    }

    public function testDimensions()
    {
        $client = static::createClient();
        $client->followRedirects(true);
        $client->xmlHttpRequest(
            'GET',
            '/api/v1/sheets/1/dimensions'
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseHeaderSame('Content-type', 'application/json');

        $responseJson = $client->getResponse()->getContent();
        $this->assertJson($responseJson);

        $responseData = json_decode($responseJson, true);
        $this->assertArrayHasKey('rows', $responseData);
        $this->assertArrayHasKey('cols', $responseData);
    }
}
