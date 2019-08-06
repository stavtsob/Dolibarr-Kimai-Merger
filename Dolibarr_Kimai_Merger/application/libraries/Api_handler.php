<?php
    require 'vendor/autoload.php';
    use GuzzleHttp\Client;
    defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Api_handler
    {   
        private $ci;
        private $settings;

        public function __construct()
        {
            $this->ci =& get_instance();
            $this->ci->config->load('tt_config', TRUE);
            $this->settings = $this->ci->config->item('tt_config');
        }
        // NOTE:
        // We are using Guzzle for sending requests.
        //
        function callDOLAPI($method, $url, $body=false)
        {
            $client = new Client([
                'base_uri' => $this->settings['dolibarr_uri'],
                'timeout'  => 2.0,
            ]);
            if($method != "POST")
            {
                if($body != false)
                {
                    $response = $client->request($method, $url.'&DOLAPIKEY='.$this->settings['DOLAPIKEY'],['body'=>$body]);
                }
                else
                {
                    $response = $client->request($method, $url.'&DOLAPIKEY='.$this->settings['DOLAPIKEY']);
                }
            }
            else
            {   
                $headers = ['DOLAPIKEY' => $this->settings['DOLAPIKEY']];
                if($body != false)
                {
                    $response = $client->request($method, $url,['headers'=>$headers,'body'=>$body]);
                }
                else
                {
                    $response = $client->request($method, $url);
                }
            }
            $result = json_decode((string) $response->getBody(), true);
            return $result;
            }
        function callKimaiAPI($method, $url, $body=false)
        {
            $client = new Client([
                'base_uri' => $this->settings['kimai_uri'],
                'timeout'  => 2.0,
            ]);
            $headers = ['X-AUTH-USER' => $this->settings['kimai_user'],'X-AUTH-TOKEN' => $this->settings['kimai_token']];
            if($body != false)
            {
                $response = $client->request($method, $url,['headers'=>$headers,'body'=>$body]);
            }
            else
            {
                $response = $client->request($method, $url,['headers'=>$headers]);
            }
            $result = json_decode((string) $response->getBody(), true);
            return $result;
        }
    }
?>