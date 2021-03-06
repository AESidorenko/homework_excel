<?php

namespace App\Tests\Controller\Api\v1;

use App\DataFixtures\MockDataHelper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CellsControllerTest extends WebTestCase
{
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;

    /**
     * CellsControllerTest constructor.
     */
    public function setUp()
    {
        $this->client = static::createClient();
        $this->client->followRedirects(true);
        $this->client->setServerParameters([
            'HTTP_X-AUTH-TOKEN' => 'test_token',
            'HTTP_Content-Type' => 'application/json'
        ]);

        self::bootKernel();
    }

    public function testRange()
    {
        $this->client->xmlHttpRequest(
            'GET',
            '/api/v1/sheets/1/cells/',
            [
                "left"   => 0,
                "top"    => 0,
                "bottom" => 4,
                "right"  => 5
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseHeaderSame('Content-type', 'application/json');

        $responseJson = $this->client->getResponse()->getContent();

        $expectedJson = ["cells" => []];
        foreach (MockDataHelper::generate2dFilledCellArray(0, 0, 4, 5) as $item) {
            $expectedJson["cells"][] = $item;
        }

        $this->assertJsonStringEqualsJsonString(json_encode($expectedJson), $responseJson);
    }

    /**
     * @depends testRange
     */
    public function testUpdate()
    {
        $this->client->xmlHttpRequest(
            'PUT',
            '/api/v1/sheets/1/cells/?row=10&col=20',
            [],
            [],
            [],
            json_encode(['value' => 30])
        );

        $this->assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @depends testUpdate
     */
    public function testDelete()
    {
        $this->client->xmlHttpRequest(
            'DELETE',
            '/api/v1/sheets/1/cells/?row=4&col=5'
        );

        $this->assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
    }
}
