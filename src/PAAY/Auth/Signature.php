<?php

class PAAY_Auth_Signature
{
    private $api_key    = null;
    private $api_secret = null;

    public function __construct($api_key, $api_secret)
    {
        $this->api_key    = $api_key;
        $this->api_secret = $api_secret;
    }

    public function get(array $payload)
    {
        if (null !== $payload) {
            if (isset($payload['api_key'])) {
                unset($payload['api_key']);
            }
            if (isset($payload['signature'])) {
                unset($payload['signature']);
            }

            //Flatten JSON (make it "onle line")
            foreach ($payload as $key => $value) {
                $decoded_value = json_decode($value, true);
                if (is_array($decoded_value)) {
                    $payload[$key] = json_encode($decoded_value, true);
                }
            }

            ksort($payload, SORT_STRING);
            $payload = join('', $payload);
        }

        return hash('sha256', $this->api_key.$payload.$this->api_secret);
    }
}
