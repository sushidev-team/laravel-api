<?php

namespace AMBERSIVE\Tests\Unit\Classes;

use \AMBERSIVE\Tests\TestPackageCase;

use AMBERSIVE\Api\Classes\SchemaEndpoint; 
use AMBERSIVE\Api\Classes\EndpointRequest; 

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Response;
use \Illuminate\Http\Response as IlluminateResponse;

use AMBERSIVE\Api\Controller\BaseApiController;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class EndpointRequestTest extends \AMBERSIVE\Tests\TestPackageCase
{


    public EndpointRequest $endpoint;

    public int $amount = 10;

    public $users;
    public $request;
    public $controller;
    
    protected function setUp(): void
    {
        parent::setUp();

        $request = Request::create('/status', 'GET', []);
        $request->headers->set('Accept', 'application/json');

        $this->request = $request;

        $this->controller = new BaseApiController($request);

        $this->endpoint = new EndpointRequest(
            $this->controller,
            \AMBERSIVE\Api\Models\User::class,
            \AMBERSIVE\Api\Resources\Users\UserResource::class,
            \AMBERSIVE\Api\Resources\Users\UserCollection::class,
            \App\Policies\Users\User::class
        );

        // Add entries
        $this->users = factory(\AMBERSIVE\Api\Models\User::class, $this->amount)->create([
            'active'            => true,
            'locked'            => false
        ]);
    }

    /**
     * Test if the model will be set correct on construction
     */
    public function testIfModelWillBeSetAtConstruction(){
        
        $this->assertNotNull($this->endpoint->getModel());
        $this->assertEquals($this->endpoint->getModel(), 'AMBERSIVE\Api\Models\User');

    }

    /**
     * Test if the resource will be set correct on construction
     */
    public function testIfResourceWillBeSetAtConstruction(){
        

        $this->assertNotNull($this->endpoint->getResource());
        $this->assertEquals($this->endpoint->getResource(), 'AMBERSIVE\Api\Resources\Users\UserResource');

    }

    /**
     * Test if the modal will be set correct on construction
     */
    public function testIfCollectionWillBeSetAtConstruction(){
        
        $this->assertNotNull($this->endpoint->getCollection());
        $this->assertEquals($this->endpoint->getCollection(), 'AMBERSIVE\Api\Resources\Users\UserCollection');

    }

    /**
     * Test if the load method will load the bucket (protected data attribute)
     */
    public function testIfEndpointRequestLoadWillFillTheDataBucket():void {

        $this->assertNull($this->endpoint->getData());
        $data = $this->endpoint->load();
        $this->assertEquals($this->endpoint->getData()->count(), $this->amount);

    }

    public function testIfEndpointRequestLoadWithAPassedIdWillASingleEntry(): void {

        $this->assertNull($this->endpoint->getData());

        $id = $this->users->first()->id;

        $endpoint = new EndpointRequest(
            $this->controller,
            \AMBERSIVE\Api\Models\User::class,
            \AMBERSIVE\Api\Resources\Users\UserResource::class,
            \AMBERSIVE\Api\Resources\Users\UserCollection::class,
            \App\Policies\Users\User::class
        );

        $data = $endpoint->load($id)->respond();

        $response = json_decode($data->getContent(), true);

        $this->assertTrue(isset($response['status']));
        $this->assertTrue(isset($response['data']));
        $this->assertTrue(isset($response['data']['id']));

    }

    /**
     * Test if the handler type can be set
     */
    public function testEndpointRequestSetHandler():void {

        $this->assertNull($this->endpoint->getHandlerType());
        $this->endpoint->handler('all');
        $this->assertEquals($this->endpoint->getHandlerType(), 'all');

    }

    /**
     * Test if a custom handler can be passed to do stuff after getting the data etc.
     */
    public function testEndpointRequestSetHandlerAcceptsAndTriggerTheCustomFn():void {

        $executed = false;

        $data = $this->endpoint->load()->handler('custom', function($ctrl, $model, $modelData) use (&$executed){

            $this->assertNotNull($ctrl);
            $this->assertNotNull($model);
            $this->assertNotNull($modelData);
            $this->assertEquals($modelData->count(), $this->amount);

            $executed = true;
        });

        $this->assertTrue($executed);

    }

    /**
     * Test if the respond method will return a valid api response with the data
     */
    public function testEndpointRequestRespondMethodIfItReturnsAValidJsonStructure(): void {

        $data = $this->endpoint->load()->respond();

        $response = json_decode($data->getContent(), true);
        $this->assertTrue(isset($response['status']));
        $this->assertTrue(isset($response['data']));
        $this->assertEquals(count(data_get($response, 'data')), $this->amount);

    }

    /**
     * Test if resource can be defiend as return value
     */
    public function testEndpointRequestRespondResourceIfRequested():void {

        $id = $this->users->first()->id;

        $data = $this->endpoint->load($id)->respond('resource');
        $dataCompare =  $this->endpoint->load($id)->respond();

        $this->assertNotEquals($data, $dataCompare);

    }

    /**
     * Test if the request header data is not null
     */
    public function testIfEndpointRequestRequestDataContainsData():void {

        $request = Request::create('/test', 'GET', []);

        $controller = new BaseApiController($request);

        $endpoint = new EndpointRequest(
            $controller,
            \AMBERSIVE\Api\Models\User::class,
            \AMBERSIVE\Api\Resources\Users\UserResource::class,
            \AMBERSIVE\Api\Resources\Users\UserCollection::class,
            null,
            $request->request
        );

        $this->assertNotNull($endpoint->getRequestData());

    }

    /**
     * Test if data can be added manually to the request data
     */
    public function testIfEndpointRequestAddRequestDataAllowsToAddData():void {

        $this->assertEmpty($this->endpoint->getRequestData());

        $this->endpoint->addRequestData(['test' => true]);

        $this->assertNotEmpty($this->endpoint->getRequestData());
        $this->assertEquals(['test' => true], $this->endpoint->getRequestData());

    }

    /**
     * This test will check in the first place if the amount of 
     * entries is the same as at the beginning
     * Than it will store an entry and will check if this new
     * entry has and id.
     * Than it will check if the total amount auf entries is + 1
     */
    public function testIfEndpointRequestStoreMethodCreateANewEntry():void {

        $this->assertEmpty($this->endpoint->getRequestData());

        $countElementsBefore = $this->endpoint->load()->getData()->count();
        $this->assertEquals($countElementsBefore, $this->amount);
        $this->endpoint->reset();

        $this->endpoint->addRequestData(['email' => 'test@test-test.com', 'username' => 'asdfasdf', 'password' => bcrypt('testtest')])->store();

        $this->assertNotNull($this->endpoint->getData());
        $this->assertNotNull(data_get($this->endpoint->getData(), 'id'));

        $this->endpoint->reset();
        $countElementsAfter = $this->endpoint->load()->getData()->count();
        $this->assertNotEquals($countElementsAfter, $countElementsBefore);

    }

    /**
     * This test will check if an entry can be deleted from the database
     * with the ->destroy method
     */
    public function testIfEndpointRequestDestroyWillDeleteAnEntry(): void {

        $this->assertEmpty($this->endpoint->getRequestData());

        $countElementsBefore = $this->endpoint->load()->getData()->count();
        $this->assertEquals($countElementsBefore, $this->amount);
        $this->endpoint->reset();

        // Delete the entry
        $id = $this->users->first()->id;
        $this->endpoint->load($id)->destroy();

        $this->endpoint->reset();

        $countElementsAfter = $this->endpoint->load()->getData()->count();
        $this->assertNotEquals($countElementsAfter, $countElementsBefore);
        $this->assertEquals($countElementsAfter, $countElementsBefore - 1);

    }

    /**
     * Test if a delete action can only be done once
     */
    public function testIfEndpointRequestDestroyWillFailForSecondTime():void {

        $this->endpoint->reset();

        // Delete the entry
        $id = $this->users->first()->id;
        $this->endpoint->load($id)->destroy();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $this->endpoint->load($id)->destroy();

    }

    /**
     * Test if the update method allows to update the model data
     */
    public function testIfEndpointReqestUpdateWillUpdateAnEntry(): void {

        $email = 'office@AMBERSIVE.com';

        $this->assertEmpty($this->endpoint->getRequestData());

        $countElementsBefore = $this->endpoint->load()->getData()->count();
        $this->assertEquals($countElementsBefore, $this->amount);
        $this->endpoint->reset();

        $userFirst = $this->users->first();
        $userFirstEmail = $userFirst->email;

        $this->endpoint->addRequestData(['email' =>  $email])->load($userFirst->id)->update();

        $countElementsAfter = $this->endpoint->load()->getData()->count();
        $this->assertEquals($countElementsBefore, $this->amount);
        $this->endpoint->reset();

        // Compare the entries
        $this->endpoint->load($userFirst->id);
        $this->assertEquals(data_get($this->endpoint->getData(), 'email'),  $email);

    }

}
