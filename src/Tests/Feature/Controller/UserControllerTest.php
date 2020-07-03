<?php

namespace AMBERSIVE\Api\Tests\Feature\Controller;

use Artisan;
use Config;
use DB;

use \AMBERSIVE\Api\Tests\TestPackageCase;

use AMBERSIVE\Api\Classes\SchemaEndpoint; 
use AMBERSIVE\Api\Classes\EndpointRequest; 

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserControllerTest extends \AMBERSIVE\Api\Tests\TestPackageCase
{

    use DatabaseMigrations;
    use DatabaseTransactions;
    
    public $user;
    public $user2;
    public $userDefaultPw = 'testtest';

    public function setUp(): void
    {
        parent::setUp();        

        // Default user
        $this->user = factory(\AMBERSIVE\Api\Models\User::class)->create([
            'username'          => 'Default',
            'email_verified_at' => now(),
            'password'          => bcrypt($this->userDefaultPw),
            'active'            => true,
            'locked'            => false
        ]);

        $this->user2 = factory(\AMBERSIVE\Api\Models\User::class)->create([
            'username'          => 'Default2',
            'email'             => 'test2@test.com',
            'email_verified_at' => now(),
            'password'          => bcrypt($this->userDefaultPw),
            'active'            => true,
            'locked'            => false
        ]);

        // Give users the permission
        $permissions = $this->generatePermission('users', ["-current"],'Users', [$this->user, $this->user2]);

    }
     
    /**
     * This test will return the current logged in user
     */
    public function testIfCurrentReturnsTheCurrentUser():void {

        $response = $this->actingAs($this->user)->getJson('/api/users/current');
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'status',
            'data' => [
                'id',
                'firstname',
                'lastname',
                'email'
            ]
        ]);

        $content = json_decode($response->getContent(), true);

        $this->assertNull(data_get($content, 'data.password'));
        $this->assertEquals(data_get($content, 'data.id'), $this->user->id);

    }
    
    /**
     * This test will check if only authenticated can get this information
     *
     * @return void
     */
    public function testIfAnUnauthorizedRequestWillRecieveA401IfCurrentIsRequested():void {

        $response = $this->getJson('/api/users/current');
        $response->assertStatus(401);

    }


    /**
     * This test will check if to seperat requests will not return the same user
     */
    public function testIfAnotherUserCannotGetTheDataForAnotherUser():void {

        // First request $user1
        $response = $this->actingAs($this->user)->getJson('/api/users/current');
        $response->assertStatus(200);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(data_get($content, 'data.id'), $this->user->id);

        // Second request $user2
        $response = $this->actingAs($this->user2)->getJson('/api/users/current');
        $response->assertStatus(200);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(data_get($content, 'data.id'), $this->user2->id);
        $this->assertNotEquals(data_get($content, 'data.id'), $this->user->id);

    }

    /**
     * Test if a user with a specific permission has access to his account data
     */
    public function testIfUserHasThePermissionToAccessTheDataOfHisAccount():void {

        $userWithNoPermissions = factory(\AMBERSIVE\Api\Models\User::class)->create([
            'username'          => 'Default2',
            'email'             => 'test3@test.com',
            'email_verified_at' => now(),
            'password'          => bcrypt($this->userDefaultPw),
            'active'            => true,
            'locked'            => false
        ]);

        $userWithNoPermissions->syncPermissions(['users-current']);

        $response = $this->actingAs($userWithNoPermissions)->getJson('/api/users/current');
        $response->assertStatus(200);

    }

    /**
     * Test if a user without the specific permission (users-current) does not have access
     * to his or her data
     */
    public function testIfUserCannotAccessHisDataIfPermissionsIsMissing():void {

        $userWithNoPermissions = factory(\AMBERSIVE\Api\Models\User::class)->create([
            'username'          => 'Default2',
            'email'             => 'test3@test.com',
            'email_verified_at' => now(),
            'password'          => bcrypt($this->userDefaultPw),
            'active'            => true,
            'locked'            => false
        ]);

        $userWithNoPermissions->syncPermissions([]);

        $response = $this->actingAs($userWithNoPermissions)->getJson('/api/users/current');
        $response->assertStatus(403);

    }

    

}
