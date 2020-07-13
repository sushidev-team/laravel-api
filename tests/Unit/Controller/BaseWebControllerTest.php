<?php

namespace AMBERSIVE\ApiTests\Unit\Contoller;

use File;

use AMBERSIVE\Tests\TestPackageCase;

use AMBERSIVE\Api\Controller\BaseWebController;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Response;
use \Illuminate\Http\Response as IlluminateResponse;

class BaseWebControllerTest extends TestPackageCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * This tests if the get return format should not return a null as value.
     * @return void
     */
    public function testRespondFileAsDownloadShouldReturnFile(): void
    {

        $request = Request::create('/', 'GET', []);

        $controller = new BaseWebController($request);

        $response = $controller->respondFileAsDownload($request, base_path('composer.json'));
    
        $this->assertTrue($response->headers->get(0) == 'Content-Type: application/json');
        $this->assertTrue($response->headers->get('content-disposition') == 'attachment; filename=composer.json');

    }

    /**
     * Test if a 404 execption is thrown if the file does not exists anymore
     * @return void
     */
    public function testRespondFileAsDownloadShouldReturn404IfFileDoesNotExists(): void {

        $request = Request::create('/', 'GET', []);

        $controller = new BaseWebController($request);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $response = $controller->respondFileAsDownload($request, base_path('XXX.XXX'));

    }

    /**
     * This test checks if the download file has the given attached filename
     */
    public function testIfRespondFileAsDownloadAcceptsTheProvidedFilename():void {

        $request = Request::create('/', 'GET', []);

        $controller = new BaseWebController($request);

        $response = $controller->respondFileAsDownload($request, base_path('composer.json'),'XXX.json');
    
        $this->assertTrue($response->headers->get(0) == 'Content-Type: application/json');
        $this->assertTrue($response->headers->get('content-disposition') == 'attachment; filename=XXX.json');        

    }

    /**
     * Test if respondFileInBrowser will return the file in browser
     */
    public function testRespondFileInBrowserShouldReturnFile(): void
    {

        $request = Request::create('/', 'GET', []);

        $controller = new BaseWebController($request);

        $response = $controller->respondFileInBrowser($request, base_path('composer.json'));    

        $this->assertTrue($response->headers->get(0) == 'Content-Type: application/json');

    }

    /**
     * Test if the respondFileInBrowser will return a markdown file as html (rendered)
     */
    public function testRespondFileInBrowserShouldReturnHtmlForMarkdown(): void
    {

        File::put(base_path("README.md"), "TEST");

        $request = Request::create('/', 'GET', []);

        $controller = new BaseWebController($request);

        $response = $controller->respondFileInBrowser($request, base_path('README.md'));    
        $this->assertTrue($response->headers->get(0) == 'Content-Type: text/html');

    }

}
