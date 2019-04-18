<?php

class MoSubscribe{
    var $server;

    public function __construct($server){
        $this->server = $server; // Assign server url
    }

    /*
        Get parameters form the application
        Send them to paramsToJson
    **/

    public function subscription($applicationId, $password, $version, $action, $msisdn){
        // Register or Deregister from the service. 
        // Opt out of the subscription
        
        return $this->paramsToJson($applicationId, $password, $version, $action, $msisdn);
        
    }

    public function checkSubscriptionStatus($applicationId, $password, $msisdn)
    {
        $this->paramsToJson($applicationId, $password, $msisdn);
    }

    public function getSubscriptionStatus()
    {
        $array = json_decode(file_get_contents('php://input'), true);
        $version = $array['version'];
        $subscriptionStatus = $array['subscriptionStatus'];
        if($subscriptionStatus == "REGISTERED")
        {
            return true;
        } else 
        {
            return false;
        }
    }

    /*
        Get parameters form the ussd
        Assign them to an array according to json format
        encode that array to json format
        Send json to sendRequest
    **/

    private function paramsToJson($applicationId, $password, $version, $action, $msisdn){

        $arrayField = array(
            "applicationId" => $applicationId,
            "password" => $password,
            "version" => $version,
            "action" => $action,
            'subscriberId'  => $msisdn
        );

        $jsonObjectFields = json_encode($arrayField);
        return $this->sendRequest($jsonObjectFields);
    }

    /*
        Get the json request from paramsToJson
        use curl methods to send Ussd
        Send the response to handleResponse
    **/

    private function sendRequest($jsonObjectFields){
        $ch = curl_init($this->server);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonObjectFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch); //Send request and get response
        curl_close($ch);
        return $this->handleResponse($res);
    }

    /*
        Get the response from sendRequest
        check response is empty
        return response
    **/

    private function handleResponse($resp){
        if ($resp == "") {
            throw new SubsException("Server URL is invalid", '500');
        } else {
            echo $resp;
        }
    }

}

class SubsException
 extends Exception{ // Ussd Exception Handler

    var $code;
    var $response;
    var $statusMessage;

    public function __construct($message, $code, $response = null){
        parent::__construct($message);
        $this->statusMessage = $message;
        $this->code = $code;
        $this->response = $response;
    }

    public function getStatusCode(){
        return $this->code;
    }

    public function getStatusMessage(){
        return $this->statusMessage;
    }

    public function getRawResponse(){
        return $this->response;
    }

}

?>