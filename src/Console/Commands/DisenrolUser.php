<?php

namespace BoxedCode\Laravel\Auth\Challenge\Console\Commands;

use BoxedCode\Laravel\Auth\Challenge\Contracts\AuthBroker;
use BoxedCode\Laravel\Auth\Challenge\Contracts\AuthManager;
use BoxedCode\Laravel\Auth\Challenge\Exceptions\ChallengeLogicException;
use Illuminate\Console\Command;
use InvalidArgumentException;

class DisenrolUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenge:disenrol {user_id : The primary key of the user to enrol}
                                               {method : The authentication method to enrol the user to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dienrol a user from 2FA.';

    /**
     * The authentication broker instance.
     * 
     * @var \BoxedCode\Laravel\Auth\Challenge\Contracts\AuthBroker
     */
    protected $broker;

    /**
     * The authentication manager instance.
     * 
     * @var \BoxedCode\Laravel\Auth\Challenge\Contracts\AuthManager
     */
    protected $manager;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(AuthBroker $broker, AuthManager $manager)
    {
        $this->broker = $broker;

        $this->manager = $manager;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $userModelName = $this->manager->getModelForGuard();

        // Find the user.
        if (!$user = $userModelName::find($this->argument('user_id'))) {
            throw new InvalidArgumentException(
                sprintf('Could not find the requested user. [%s]', $this->argument('user_id'))
            );
        }

        // Enrol the user.
        $this->handleBrokerResponse(
            $this->broker->disenrol($user, $this->argument('method'))
        );
    }

    /**
     * Handle responses from the broker.
     * 
     * @param  AuthBrokerResponse $response
     * @return void
     */
    protected function handleBrokerResponse($response)
    {
        switch ($response) 
        {
            case AuthBroker::ENROLMENT_NOT_FOUND:
                $this->error(
                    'The user is not enrolled in the provided authentication method.'
                );
                return;

            case AuthBroker::USER_DISENROLLED:
                $this->info(
                    sprintf(
                        'The user id %s was successfully disenrolled from %s.', 
                        $response->enrolment->user->getKey(), 
                        $response->enrolment->method
                    )
                );
                return;
        }

        throw new ChallengeLogicException(
            sprintf('The broker returned an invalid response. [%s]', $response)
        );
    }
}
