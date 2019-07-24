<?php

namespace BoxedCode\Laravel\TwoFactor;

class BrokerResponse
{
    /**
     * The outcome of the response.
     * 
     * @var sting
     */
    protected $outcome;

    /**
     * Additional data related to the response.
     * 
     * @var array
     */
    protected $payload;

    /**
     * Create a new response instance.
     * 
     * @param string $outcome
     * @param array  $payload
     */
    public function __construct(string $outcome, array $payload = [])
    {
        $this->outcome = $outcome;

        $this->payload = $payload;
    }

    /**
     * Get the data associated with the response.
     * 
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Dynamically route calls to the responses payload.
     * 
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->payload)) {
            return $this->payload[$name];
        }
    }

    /**
     * Get a string representation of the response, 
     * always the outcome.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->outcome;
    }
}