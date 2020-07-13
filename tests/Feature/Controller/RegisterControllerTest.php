<?php

namespace AMBERSIVE\Tests\Feature\Controller;

use Artisan;
use Config;
use Mockery;
use Str;

use \AMBERSIVE\Tests\TestPackageCase;

use AMBERSIVE\Api\Classes\SchemaEndpoint; 
use AMBERSIVE\Api\Classes\EndpointRequest; 

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Illuminate\Support\Facades\Mail;

use Faker\Generator as Faker;

class RegisterControllerTest extends \AMBERSIVE\Tests\TestPackageCase
{

    use DatabaseMigrations;
    use DatabaseTransactions;
    
    public $user;
    public array $form;
    public array $formInvalid;
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

        $this->generateRoles(['User']);

        // Setup registration

        $faker = \Faker\Factory::create();

        $password = $faker->password(8);

        $this->form = [
            'username'              => 'Test',
            'email'                 => 'test2@test.com',
            'password'              => $password,
            'password_confirmation' => $password,
            'language'              => 'en'
        ];

        $this->formInvalid = [
            'username'              => 'Test',
            'email'                 => 'test@test.com',
            'password'              => $password,
            'password_confirmation' => $password,
            'language'              => 'en'
        ];

    }

    /**
     * This test should return a 422 due to the fact that 
     * the validation fails
     */
    public function testIfRegistrationFailsDueValidation():void {

        $response = $this->postJson('/api/auth/register', []);
        $response->assertStatus(422);

    }    

    /**
     * Test if the validation check tests for uniqueness of email address
     */
    public function testIfRegistrationFailsIfEmailAlreadyUsed(): void {

        $response = $this->postJson('/api/auth/register', $this->formInvalid);
        $response->assertStatus(422);

        $json = json_decode($response->getContent(), true);

        $this->assertEquals(1, sizeOf(data_get($json, 'errors')));
        $this->assertNotNull(data_get($json, 'errors.email'));

    }

    /**
     * Test if the validation check tests for uniqueness of email address
     */
    public function testIfRegistrationFailsIfUsernameAlreadyUsed(): void {

        $response = $this->postJson('/api/auth/register', [
            'username'              => 'Default',
            'email'                 => 'test2@test.com',
            'password'              => $this->userDefaultPw,
            'password_confirmation' => $this->userDefaultPw,
            'language'              => 'en'
        ]);

        $response->assertStatus(422);

        $json = json_decode($response->getContent(), true);

        $this->assertEquals(1, sizeOf(data_get($json, 'errors')));
        $this->assertNotNull(data_get($json, 'errors.username'));

    }

    /**
     * Test if a user account can be created
     */
    public function testIfRegistrationWillCreateAccount(): void {

        $response = $this->postJson('/api/auth/register', $this->form);
        $response->assertStatus(200);

        $json = json_decode($response->getContent(), true);

        $this->assertNotNull($json);

        $this->assertEquals(data_get($this->form, 'username'), data_get($json, 'data.username'));
        $this->assertEquals(data_get($this->form, 'email'), data_get($json, 'data.email'));

    }

    /**
     * Test if active flag will not be active by default
     */
    public function testIfRegisteredUserWillNotBeActive(): void {

        $response = $this->postJson('/api/auth/register', $this->form);
        $response->assertStatus(200);

        $json = json_decode($response->getContent(), true);

        $id = data_get($json, 'data.id');

        $user = \AMBERSIVE\Api\Models\User::where('id', $id)->first();
        $userActivation = \AMBERSIVE\Api\Models\UserActivation::where('user_id', $user->id)->first();

        $this->assertNotNull($user);
        $this->assertFalse($user->active);

    }

    /**
     * This test will check if an activation mail will be sent to the users
     */
    public function testIfRegistrationWithoutAutomaticActivationWillSendAnActivationMail():void {
        
        Mail::fake(\AMBERSIVE\Api\Mails\ActivationMail::class);
        Mail::assertNothingSent();

        $response = $this->postJson('/api/auth/register', $this->form);
        $response->assertStatus(200);

        $json = json_decode($response->getContent(), true);

        $id = data_get($json, 'data.id');
        $user = \AMBERSIVE\Api\Models\User::where('id', $id)->first();

        Mail::assertSent(\AMBERSIVE\Api\Mails\ActivationMail::class , function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

    }

    /**
     * This test will check if the complete activation process works
     */
    public function testIfRegistrationFlowWithActivationWorks(): void {

        $response = $this->postJson('/api/auth/register', $this->form);
        $response->assertStatus(200);

        // Test if the user can be activated

        $id = data_get(json_decode($response->getContent(), true), 'data.id');
        $userActivation = \AMBERSIVE\Api\Models\UserActivation::where('user_id', $id)->first();
        $code = $userActivation->code;

        $responseActivation = $this->getJson("/api/auth/activation/${code}");
        $responseActivation->assertStatus(200);

        // Test if the user is active
        $user = \AMBERSIVE\Api\Models\User::where('id', $id)->first();
        $this->assertTrue($user->active);

    }
     
    /**
     * Test if the activation process will redirect a given route if requested via "Accept:text/html"
     */
    public function testIfRegistrationFlowWithAcceptHtmlWillCauseAnRedirect(): void {

        $response = $this->postJson('/api/auth/register', $this->form);
        $response->assertStatus(200);

        // Test if the user can be activated

        $id = data_get(json_decode($response->getContent(), true), 'data.id');
        $userActivation = \AMBERSIVE\Api\Models\UserActivation::where('user_id', $id)->first();
        $code = $userActivation->code;

        $responseActivation = $this->withHeaders([
            'Accept' => 'text/html',
        ])->get("/api/auth/activation/${code}");

        $responseActivation->assertStatus(302);
        $responseActivation->assertRedirect(config('ambersive-api.registration_redirect_success', '/'));
        
    }

    /**
     * Test if the activation proccess will fail if the user is locked
     */
    public function testIfActivationWillFailIfUserIsLocked():void {

        $response = $this->postJson('/api/auth/register', $this->form);
        $response->assertStatus(200);

        $json = json_decode($response->getContent(), true);

        $id = data_get($json, 'data.id');
        $user = \AMBERSIVE\Api\Models\User::where('id', $id)->first();
        $user->locked = true;
        $user->save();

        $userActivation = \AMBERSIVE\Api\Models\UserActivation::where('user_id', $id)->first();
        $code = $userActivation->code;

        // Try activaton

        $responseActivation = $this->getJson("/api/auth/activation/${code}");
        $responseActivation->assertStatus(400);

    }

    /**
     * Test if the activation process will fail if the user was already verified
     */
    public function testIfActivationWillFailIfUserIsAlreadyVerified():void {

        $response = $this->postJson('/api/auth/register', $this->form);
        $response->assertStatus(200);

        $json = json_decode($response->getContent(), true);

        $id = data_get($json, 'data.id');
        $user = \AMBERSIVE\Api\Models\User::where('id', $id)->first();
        $user->email_verified_at = now();
        $user->save();

        $userActivation = \AMBERSIVE\Api\Models\UserActivation::where('user_id', $id)->first();
        $code = $userActivation->code;

        // Try activaton

        $responseActivation = $this->getJson("/api/auth/activation/${code}");
        $responseActivation->assertStatus(400);

    }

    /**
     * Test if the activation process will fail and will redirect to the failure site
     */
    public function testIfActivationWillFailAndWillRedirectToFailureSite():void {

        $response = $this->postJson('/api/auth/register', $this->form);
        $response->assertStatus(200);

        $json = json_decode($response->getContent(), true);

        $id = data_get($json, 'data.id');
        $user = \AMBERSIVE\Api\Models\User::where('id', $id)->first();
        $user->locked = true;
        $user->save();

        $userActivation = \AMBERSIVE\Api\Models\UserActivation::where('user_id', $id)->first();
        $code = $userActivation->code;

        // should trigger a redirect (302)       

        $responseActivation = $this->withHeaders([
            'Accept' => 'text/html',
        ])->get("/api/auth/activation/${code}");

        $responseActivation->assertStatus(302);
        $responseActivation->assertRedirect(config('ambersive-api.registration_redirect_failure', '/'));

    }

}
