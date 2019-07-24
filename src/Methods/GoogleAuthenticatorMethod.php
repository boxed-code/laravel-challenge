<?php
//@todo Customer QR code generator.
//@todo Pass method name into method constructors
//@todo Customer method labels

namespace BoxedCode\Laravel\TwoFactor\Methods;

use BoxedCode\Laravel\TwoFactor\Contracts\Challengeable;
use BoxedCode\Laravel\TwoFactor\Contracts\Method as MethodContract;
use BoxedCode\Laravel\TwoFactor\Exceptions\TwoFactorVerificationException;
use BoxedCode\Laravel\TwoFactor\Methods\Method;
use PragmaRX\Google2FA\Google2FA;

class GoogleAuthenticatorMethod extends Method implements MethodContract
{
    /**
     * Gets whether the method should require a 
     * successful challenge before enrolling the user.
     * 
     * @return bool
     */
    public function requiresEnrolmentChallenge()
    {
        return true;
    }

    /**
     * Gets whether the method needs to be 
     * setup during enrolment.
     * 
     * @return bool
     */
    public function requiresEnrolmentSetup()
    {
        return true;
    }

    /**
     * Perform any pre-setup processing and return any data required by 
     * the user before setup.
     * 
     * @param  Challengeable $user
     * @return array
     */
    public function beforeSetup(Challengeable $user): array
    {
        $google = new Google2FA();

        $key = $google->generateSecretKey($this->config['key_size'] ?? 32);

        $qrCodeUrl = $google->getQRCodeUrl(
            config()->get('app.name'),
            $user->email,
            $key
        );

        $url = base64_encode(
            file_get_contents(
                "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . 
                    urlencode($qrCodeUrl)
            )
        );

        $state = ['secret' => $key];

        $data = ['qr_png' => 'data:image/png;base64,' . $url];

        return [$state, $data];
    }

    /**
     * Process the provided setup $data and return any additional state data 
     * that will be merged and persisted with the enrolments existing state.
     * 
     * @param  Challengeable $user  
     * @param  array         $state 
     * @param  array         $data  
     * @return array               
     */
    public function setup(Challengeable $user, array $state = [], array $data = []): array
    {
        return [];
    }

    /**
     * Perform any actions required to enrol the user into the 
     * authentication method and return any additional state data 
     * that will be merged and persisted with the enrolments
     * existing state.
     * 
     * @param  Challengeable $user  
     * @param  array         $state 
     * @return state               
     */
    public function enrol(Challengeable $user, array $state = []): array
    {
        return [];
    }

    /**
     * Perform any actions to disenrol the user from the authentication method.
     * 
     * @param  Challengeable $user  
     * @param  array         $state 
     * @return void               
     */
    public function disenrol(Challengeable $user, array $state = [])
    {
        return [];
    }

    /**
     * Dispatch a challenge via the method to the supplied user and return any 
     * additional state data that will be merged and persisted with the 
     * challenges existing state.
     * 
     * @param  Challengeable $user 
     * @param  array         $data 
     * @return array              
     */
    public function challenge(Challengeable $user, array $data = []): array
    {
        return [];
    }

    /**
     * Verify the challenge by validating supplied $data and challenge $state, 
     * if it is not valid throw a TwoFactorVerificationException. 
     * 
     * If it is valid, return any additional state data that will be merged and 
     * persisted with the challenges existing state.
     * 
     * @param  Challengeable $user  
     * @param  array         $state 
     * @param  array         $data  
     * @return aray               
     */
    public function verify(Challengeable $user, array $state = [], array $data = []): array
    {
        $google = new Google2FA();

        // 'authenticator' needs to be swapped for the actual method key, 
        // we need to pass this in somewhere.
        $enrolment = $user->enrolments()->method('authenticator')->get()->first();

        $window = $this->config['window'] ?? 4;

        $secret = $enrolment ? $enrolment->state['secret'] : '';

        $code = $data['code'] ?? '';

        if ($enrolment && $google->verifyKey($secret, $code, $window)) {
            return [];
        }

        throw new TwoFactorVerificationException;
    }
}