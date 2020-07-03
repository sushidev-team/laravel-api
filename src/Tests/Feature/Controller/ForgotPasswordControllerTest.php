<?php

namespace AMBERSIVE\Api\Tests\Feature\Controller;

use Artisan;
use Config;
use Mail;

use Carbon\Carbon;

use \AMBERSIVE\Api\Tests\TestPackageCase;

use AMBERSIVE\Api\Classes\SchemaEndpoint; 
use AMBERSIVE\Api\Classes\EndpointRequest; 

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use \AMBERSIVE\Api\Models\PasswordReset;

class ForgotPasswordControllerTest extends \AMBERSIVE\Api\Tests\TestPackageCase
{

    use DatabaseMigrations;
    use DatabaseTransactions;
    
    public $user;
    public String $userDefaultPw = 'testtest';

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
     * Test if the api respond for trying to request a reset code will return HTTP_OK
     */
    public function testIfForgotPasswordWillReturnStatusSuccessfulIfAccountExists():void {

        $response = $this->postJson('/api/auth/password/forgotten', [
            'email'    => $this->user->email
        ]);

        $response->assertStatus(200);

    }

    /**
     * Test if the api will respond with HTPP_OK even if the related email address does not exists.
     */
    public function testIfForgotPasswordWillReturnStatusSuccessfulEvenIfAccountDoesnotExist():void {

        $response = $this->postJson('/api/auth/password/forgotten', [
            'email'    => 'xxx@test.de'
        ]);

        $response->assertStatus(200);

    }

    /**
     * Test if the forgot password process will trigger an event
     */
    public function testIfForgotPasswordWillTriggerEvent():void {
        
        $this->expectsEvents(\AMBERSIVE\Api\Events\ForgotPassword::class);

        $response = $this->postJson('/api/auth/password/forgotten', [
            'email'    => $this->user->email
        ]);

        $response->assertStatus(200);

    }

    /**
     * Test if the forgot password process will trigger an event and a listener for 
     * this event exists
     */
    public function testIfForgotPasswordEventListenerReactsToEvent():void {

        $this->expectsEvents(\AMBERSIVE\Api\Events\ForgotPassword::class);

        $response = $this->postJson('/api/auth/password/forgotten', [
            'email'    => $this->user->email
        ]);

        $response->assertStatus(200);

    }

    /**
     * Test if the process will create an reset code in the database
     */
    public function testIfForgotPasswordWillCreateAndEntryInDatabaseWithResetCode():void {

        $response = $this->postJson('/api/auth/password/forgotten', [
            'email'    => $this->user->email
        ]);

        $resetCode = PasswordReset::all()->first();

        $this->assertNotNUll($resetCode);
        $this->assertEquals($this->user->id, $resetCode->user_id);

    }

    /**
     * Test if the forgot password process will send out an email to
     * the related email address
     */
    public function testIfForgotPasswordWillTriggerSendingAnEmail():void {

        Mail::fake(\AMBERSIVE\Api\Mails\ResetPasswordMail::class);
        Mail::assertNothingSent();

        $response = $this->postJson('/api/auth/password/forgotten', [
            'email'    => $this->user->email
        ]);

        Mail::assertSent(\AMBERSIVE\Api\Mails\ResetPasswordMail::class , function ($mail) {
            return $mail->hasTo($this->user->email);
        });

    }

    /**
     * Test if the mail contains a reset code
     */
    public function testIfForgotPasswordMailWillContainAResetCode():void {


        Mail::fake(\AMBERSIVE\Api\Mails\ResetPasswordMail::class);
        Mail::assertNothingSent();

        $response = $this->postJson('/api/auth/password/forgotten', [
            'email'    => $this->user->email
        ]);

        Mail::assertSent(\AMBERSIVE\Api\Mails\ResetPasswordMail::class , function ($mail) {
            $filled = $mail->build();
            $this->assertNotNull($filled->viewData['code']);
            return $mail->hasTo($this->user->email);
        });

    }

    /**
     * Test if the user id or the email adress for reseting the password is required
     */
    public function testIfSetPasswordNeedsEmailOrUserId():void {

        $response = $this->postJson('/api/auth/password', []);

        $json = json_decode($response->getContent(), true);
        $fields = array_keys(data_get($json, 'errors', []));

        $response->assertStatus(422);
        $this->assertContains('user_id', $fields);
        $this->assertContains('email', $fields);

    }

    /**
     * Test if the set password method will fail if there was no old password passed
     */
    public function testIfSetPasswordFailsIfNotOldPasswordIsGiven():void {

        $response = $this->postJson('/api/auth/password', [
            'user_id'    => $this->user->id
        ]);

        $json = json_decode($response->getContent(), true);
        $fields = array_keys(data_get($json, 'errors', []));

        $response->assertStatus(422);

        $this->assertContains('password_old', $fields);

    }

    /**
     * Test if the controller throw an 422 if there is no reset_code passed
     */
    public function testIfSetPasswordFailsAlsoIfNoResetCodeIsProvided():void {

        $response = $this->postJson('/api/auth/password', [
            'user_id'    => $this->user->id
        ]);

        $json = json_decode($response->getContent(), true);
        $fields = array_keys(data_get($json, 'errors', []));

        $this->assertContains('password_old', $fields);

    }

    /**
     * Test if validaton checks if the given user_id exists
     */
    public function testIfSetPasswordChecksIfUserIdExists():void {

        $response = $this->postJson('/api/auth/password', [
            'user_id'    => 'XXX'
        ]);

        $json = json_decode($response->getContent(), true);
        $fields = array_keys(data_get($json, 'errors', []));

        $response->assertStatus(422);
        $this->assertContains('user_id', $fields);

    }

    /**
     * Test if validaton checks if the given email address exists
     */
    public function testIfSetPasswordChecksIfEmailExists():void {

        $response = $this->postJson('/api/auth/password', [
            'email'    => 'XXX@XXX.com'
        ]);

        $json = json_decode($response->getContent(), true);
        $fields = array_keys(data_get($json, 'errors', []));

        $response->assertStatus(422);
        $this->assertContains('email', $fields);

    }

    /**
     * Test if the controller checks if the reset code was already used
     */
    public function testIfSetPasswordFailsIfTheResetCodeIsAlreadyUsed():void {

        $code = factory(\AMBERSIVE\Api\Models\PasswordReset::class)->create(['user_id' => $this->user->id, 'used' => true]);

        $response = $this->postJson('/api/auth/password', [
            'user_id'               => $this->user->id,
            'code'                  => $code->code,
            'password'              => 'testtest',
            'password_confirmation' => 'testtest'
        ]);

        $response->assertStatus(400);

        $json = json_decode($response->getContent(), true);
        $message = data_get($json, 'data.message', null);

        $this->assertEquals(__('ambersive-api::users.password.setpassword.used'), $message);

    }
    
    /**
     * Test if an unused reset code which is older than allowed will
     * result in an failure.
     */
    public function testIfSetPasswordFailsIfTheResetCodeIsExpired():void {

        $code = factory(\AMBERSIVE\Api\Models\PasswordReset::class)->create(['user_id' => $this->user->id, 'used' => false, 'created_at' => Carbon::now()->sub(5000, 'minutes')]);

        $response = $this->postJson('/api/auth/password', [
            'user_id'               => $this->user->id,
            'code'                  => $code->code,
            'password'              => 'testtest',
            'password_confirmation' => 'testtest'
        ]);

        $response->assertStatus(400);

        $json = json_decode($response->getContent(), true);
        $message = data_get($json, 'data.message', null);

        $this->assertEquals(__('ambersive-api::users.password.setpassword.expired'), $message);

    }

    /**
     * Test if the code will not return an expired code
     */
    public function testIfSetPasswordWillNotReturnExpiredTokenIfRequestIsWithinTheTimeframe():void {

        $code = factory(\AMBERSIVE\Api\Models\PasswordReset::class)->create(['user_id' => $this->user->id, 'used' => false, 'created_at' => Carbon::now()->sub(1439, 'minutes')]);

        $response = $this->postJson('/api/auth/password', [
            'user_id'               => $this->user->id,
            'code'                  => $code->code,
            'password'              => 'testtest',
            'password_confirmation' => 'testtest'
        ]);

        $json = json_decode($response->getContent(), true);
        $message = data_get($json, 'data.message', null);

        $this->assertNotEquals(__('ambersive-api::users.password.setpassword.expired'), $message);

    }

    /**
     * Test if the set password request will fail if the passed old password is incorrect
     */
    public function testIfSetPasswordWIllFailIfTheGivenOldPasswordIsIncorrect():void {

        $code = factory(\AMBERSIVE\Api\Models\PasswordReset::class)->create(['user_id' => $this->user->id, 'used' => false, 'created_at' => Carbon::now()->sub(1439, 'minutes')]);

        $response = $this->postJson('/api/auth/password', [
            'user_id'               => $this->user->id,
            'password_old'          => 'XXXXXXXX',
            'password'              => 'testtest',
            'password_confirmation' => 'testtest'
        ]);

        $json = json_decode($response->getContent(), true);
        $message = data_get($json, 'data.message', null);

        $response->assertStatus(400);
        $this->assertEquals(__('ambersive-api::users.password.setpassword.failed'), $message);

    }

    /**
     * Test if set password works if the correct old password is passed
     */
    public function testIfSetPasswordWorksIfOldPasswordIsPassed():void {

        $response = $this->postJson('/api/auth/password', [
            'user_id'               => $this->user->id,
            'password_old'          => $this->userDefaultPw,
            'password'              => 'testtest',
            'password_confirmation' => 'testtest'
        ]);

        $response->assertStatus(200);

    }

    /**
     * Test if a password can be set if the reset code is valid
     */
    public function testIfSetPasswordWorksIfValidResetCodeIsPassed(): void {

        $code = factory(\AMBERSIVE\Api\Models\PasswordReset::class)->create(['user_id' => $this->user->id, 'used' => false, 'created_at' => Carbon::now()->sub(1439, 'minutes')]);

        $response = $this->postJson('/api/auth/password', [
            'user_id'               => $this->user->id,
            'code'                  => $code->code,
            'password'              => 'testtest',
            'password_confirmation' => 'testtest'
        ]);

        $response->assertStatus(200);

    }

}
