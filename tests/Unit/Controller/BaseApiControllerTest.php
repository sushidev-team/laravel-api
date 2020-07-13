<?php

namespace AMBERSIVE\ApiTests\Unit\Contoller;

use AMBERSIVE\Tests\TestPackageCase;
use AMBERSIVE\Api\Controller\BaseApiController;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Response;
use \Illuminate\Http\Response as IlluminateResponse;

class BaseApiControllerTest extends TestPackageCase
{

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * This tests if the get return format should not return a null as value.
     * @return void
     */
    public function testGetReturnFormatShouldReturnNotNull(): void
    {
        $request = Request::create('/status', 'GET', []);
        $request->headers->set('Accept', 'application/json');

        $controller = new BaseApiController($request);
        $format     = $controller->getReturnFormat();

        $this->assertNotNull($format);
        $this->assertEquals('application/json', $format);

    }

    /**
     * Test if the default Status Code is HTTP_OK
     */
    public function testGetDefaultStatusCode(): void {

        $request = Request::create('/status', 'GET', []);
        $request->headers->set('Accept', 'application/json');

        $controller = new BaseApiController($request);

        $this->assertNotNull($controller->getDefaultStatusCode());
        $this->assertEquals($controller->getDefaultStatusCode(), IlluminateResponse::HTTP_OK);

    }

    /**
     * This test will test if the setReturnFormat method will change the format.
     * @return void
     */
    public function testSetReturnFormatShouldChangeTheReturnFormatValue():void 
    {
        $request = Request::create('/status', 'GET', []);
        $request->headers->set('Accept', 'application/xml');

        $controller = new BaseApiController($request);

        $controller->setReturnFormat('application/json');
        $format     = $controller->getReturnFormat();

        $this->assertNotNull($format);
        $this->assertEquals('application/json', $format);
    }

    /**
     * This test will check if the controller can successfully set the download flag to true
     */
    public function testControllerParamsShouldContainDownloadFlag():void 
    {

        $request = Request::create('/status', 'GET', ['download' => true]);

        $controller = new BaseApiController($request);

        $this->assertNotNull($controller->isDownload());
        $this->assertTrue($controller->isDownload());

    }

    /**
     * This test will check if the controller sets the download to false if the given parameter is null
     * @return void
     */
    public function testControllerParamsShouldContainDownloadFlagWithFalse():void
    {
        $request = Request::create('/status', 'GET', ['download' => null], []);
        
        $controller = new BaseApiController($request);

        $this->assertNotNull($controller->isDownload());
        $this->assertFalse($controller->isDownload());

    }

    public function testSettingTheStatusCode():void {

        $request = Request::create('/status', 'GET', ['download' => null], []);
        $controller = new BaseApiController($request);

        $controller->setStatus(IlluminateResponse::HTTP_OK);

        $this->assertNotNull($controller->getStatus());
        $this->assertEquals($controller->getStatus(), IlluminateResponse::HTTP_OK);

    }

    /**
     * Test if the respond respondSuccess returns a response with 
     * http status code 200
     */
    public function testRespondSuccess():void {

        $request = Request::create('/status', 'GET', ['download' => null], []);
        $controller = new BaseApiController($request);

        $response = $controller->respondSuccess(['message' => 'SUCCESS']);

        $this->assertNotNull($response);
        $this->assertEquals($response->status(), 200);

        $json = json_decode($response->getContent(), true);

        $this->assertEquals(data_get($json, 'data.message'), 'SUCCESS');

    }

    /**
     * Test if the respond respondUnauthorized returns a response with 
     * http status code 401
     */
    public function testRespondUnauthorized():void {

        $request = Request::create('/status', 'GET', ['download' => null], []);
        $controller = new BaseApiController($request);

        $response = $controller->respondUnauthorized(['message' => 'Bad Request']);

        $this->assertNotNull($response);
        $this->assertEquals($response->status(), IlluminateResponse::HTTP_UNAUTHORIZED);
        
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(data_get($json, 'data.message'), 'Bad Request');

    }

    /**
     * Test if the respond respondForbidden returns a response with 
     * http status code 403
     */
    public function testRespondForbidden():void {

        $request = Request::create('/status', 'GET', ['download' => null], []);
        $controller = new BaseApiController($request);

        $response = $controller->respondForbidden(['message' => 'FORBIDDEN']);

        $this->assertNotNull($response);
        $this->assertEquals($response->status(), IlluminateResponse::HTTP_FORBIDDEN);
        
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(data_get($json, 'data.message'), 'FORBIDDEN');

    }

    /**
     * Test if the respond respondForbidden returns a response with 
     * http status code 403
     */
    public function testRespondBadRequest():void {

        $request = Request::create('/status', 'GET', ['download' => null], []);
        $controller = new BaseApiController($request);

        $response = $controller->respondBadRequest(['message' => 'BAD REQUEST']);

        $this->assertNotNull($response);
        $this->assertEquals($response->status(), IlluminateResponse::HTTP_BAD_REQUEST);
        
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(data_get($json, 'data.message'), 'BAD REQUEST');

    }

}
