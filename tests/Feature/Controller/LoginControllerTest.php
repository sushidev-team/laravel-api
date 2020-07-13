<?php

namespace AMBERSIVE\Tests\Feature\Controller;

use Artisan;
use Config;

use \AMBERSIVE\Tests\TestPackageCase;

use AMBERSIVE\Api\Classes\SchemaEndpoint; 
use AMBERSIVE\Api\Classes\EndpointRequest; 

class LoginControllerTest extends \AMBERSIVE\Tests\TestPackageCase
{
    
    public $user;
    public string $userDefaultPw = 'testtest';

    public function setUp(): void
    {
        parent::setUp();

        // Default user
        $this->user = factory(\AMBERSIVE\Api\Models\User::class)->create([
            'username'          => 'Default',
            'email'             => 'test@test.com',
            'email_verified_at' => now(),
            'password'          => bcrypt($this->userDefaultPw),
            'active'            => true,
            'locked'            => false
        ]);

    }
     
     /**
      * Test if the login works and if the structure is defined
      *
      * @return void
      */
     public function testLoginWorksWithCorrectCredentials():void {

        $response = $this->postJson('/api/auth/login', [
            'email'    => $this->user->email,
            'password' => $this->userDefaultPw
        ]);

        $response->assertStatus(200)->assertJsonStructure([
            'data' => [
                'access_token',
                'token_type',
                'expires_in'
            ]
        ]);

     }
     
     /**
      * Test if the login endpoints requires username and password
      *
      * @return void
      */
    
     public function testIfLoginRequiresEmailAndPassword():void {
        
        $response = $this->postJson('/api/auth/login', []);
        $response->assertStatus(422);

        // Only email
        $response = $this->postJson('/api/auth/login', [
            'email' => 'email@email.de'
        ]);
        $response->assertStatus(422);

        // Only password
        $response = $this->postJson('/api/auth/login', [
            'password' => $this->userDefaultPw
        ]);
        $response->assertStatus(422);
     }
     
     /**
      * Test if the login only works with a exisits user
      *
      * @return void
      */
     public function testIfLoginRequiresAExisitingUser():void {
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test@test.de',
            'password' => $this->userDefaultPw
        ]);
        $response->assertStatus(400);
     }

     
     /**
      * Test document is testing if the user needs to be active
      *
      * @return void
      */
     public function testIfLoginRequiresAnActiveUser():void {

        $user = factory(\AMBERSIVE\Api\Models\User::class)->create([
            'username'          => 'Testcase',
            'email'             => 'test1@test.com',
            'email_verified_at' => now(),
            'password'          => bcrypt($this->userDefaultPw),
            'active'            => false,
            'locked'            => false
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test1@test.de',
            'password' => $this->userDefaultPw
        ]);

        $response->assertStatus(400)->assertJsonPath('data.message', __('ambersive-api::auth.failed'));

     }
     
     /**
      * Test if the e-mail address must be verified.
      *
      * @return void
      */
     public function testIfLoginRequiresAnUserWithAVerifiedEMail():void {

        $user = factory(\AMBERSIVE\Api\Models\User::class)->create([
            'username'          => 'Testcase',
            'password'          => bcrypt($this->userDefaultPw),
            'active'            => true,
            'locked'            => false,
            'email_verified_at' => null
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email'    => $user->email,
            'password' => $this->userDefaultPw
        ]);

        $response->assertStatus(400)->assertJsonPath('data.message', __('ambersive-api::auth.notVerified'));


     }
     
     /**
      * Test if the account has been locked. 
      *
      * @return void
      */
     public function testIfLoginRequiresAnUserWhoIsNotLocked():void {

        $user = factory(\AMBERSIVE\Api\Models\User::class)->create([
            'username'          => 'Testcase',
            'password'          => bcrypt($this->userDefaultPw),
            'active'            => true,
            'locked'            => true
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email'    => $user->email,
            'password' => $this->userDefaultPw
        ]);

        $response->assertStatus(400)->assertJsonPath('data.message', __('ambersive-api::auth.locked'));

     }
     
     /**
      * Test if an account will be locked after the maximum amount of invalid tries
      *
      * @return void
      */
     public function testIfLoginWillFailIfTriedAfterALocked():void {

        $user = factory(\AMBERSIVE\Api\Models\User::class)->create([
            'username'          => 'Testcase',
            'password'          => bcrypt($this->userDefaultPw),
            'active'            => true,
            'locked'            => false
        ]);

        Config::set('ambersive-api.login_attempts', 3);

        for ($i = 1; $i < config('ambersive-api.login_attempts') ; $i++) {
            $response = $this->postJson('/api/auth/login', ['email'    => $user->email, 'password' => 'testasdf']);
            $response->assertStatus(400)->assertJsonPath('data.message', __('ambersive-api::auth.failed'));
        }
        
        $response = $this->postJson('/api/auth/login', ['email'    => $user->email, 'password' => 'testasdf']);
        $response->assertStatus(400)->assertJsonPath('data.message', __('ambersive-api::auth.locked'));

     }
     
     /**
      * Test if account has been deleted and do not allow it.
      *
      * @return void
      */
     public function testIfLoginRequiresAUserWhoIsNotDeleted():void {

        $user = factory(\AMBERSIVE\Api\Models\User::class)->create([
            'username'          => 'Testcase',
            'password'          => bcrypt($this->userDefaultPw),
            'active'            => true,
            'locked'            => false
        ]);

        $user->delete();

        $response = $this->postJson('/api/auth/login', [
            'email'    => $user->email,
            'password' => $this->userDefaultPw
        ]);

        $response->assertStatus(400)->assertJsonPath('data.message', __('ambersive-api::auth.failed'));

     }

     /**
      * Test if the refresh token endpoint will return a access_token
      */
     public function testIfRefreshTokenReturnsANewBearerToken(): void {

        // Login to get a token for a user
        $response = $this->postJson('/api/auth/login', [
            'email'    => $this->user->email,
            'password' => $this->userDefaultPw
        ]);

        $json  = json_decode($response->getContent(), true);
        $token = data_get($json, 'data.access_token');

        // Make the attemp for the refresh token
        $responseForTesting = $this->withHeaders([
            'Authorization' => "Bearer ${token}",
        ])->getJson('/api/auth/refresh');

        $responseForTesting->assertStatus(200);

        $jsonTesting  = json_decode($responseForTesting->getContent(), true);
        $token        = data_get($jsonTesting, 'data.access_token');

        $responseForTesting->assertJsonStructure([
            'status',
            'data' => [
                'access_token',
                'token_type',
                'expires_in'
            ]
        ]);

        $this->assertNotNull($token);

     }

}
