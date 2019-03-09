<?php

include_once 'lib/sms/SmsReceiver.php';
include_once 'lib/sms/SmsSender.php';
include_once 'log.php';
ini_set('error_log', 'sms-app-error.log');
try {
    // This is where sms can be recieved.
    /*
    $receiver = new SmsReceiver(); // Create the Receiver object
    $content = $receiver->getMessage(); // get the message content
    $address = $receiver->getAddress(); // get the sender's address
    $requestId = $receiver->getRequestID(); // get the request ID
    $applicationId = $receiver->getApplicationId(); // get application ID
    $encoding = $receiver->getEncoding(); // get the encoding value
    $version = $receiver->getVersion(); // get the version
    logFile("[ content=$content, address=$address, requestId=$requestId, applicationId=$applicationId, encoding=$encoding, version=$version ]");
    */
    
    // Create the logic Here to fetch data from database 
    // and send the SMS based on taken service 
    
    $db = new PDO('mysql:host=localhost;dbname=db_ussdreminder', 'root', '');
    $query = "SELECT `msisdn`,`request_id`, `reminder_date`, `service_id` FROM `tbl_request`";
    $select = $db->prepare($query);
    //$select->bindParam(':request_id', $requestId, PDO::PARAM_INT);
    $select->execute();
    
    $queryData = $select->fetch(PDO::FETCH_ASSOC);
    $address = $queryData['msisdn'];
    $request_id = $queryData['request_id'];
    $service_id = $queryData['service_id'];
    $reminderDate = $queryData['reminder_date'];

    /*
    Insert a switch conditional statemont for each service_id
    */
    if($service_id)
    {
        switch($service_id)
        {
            case '1':
                $responseMsg = "Hi, the birthday of your friend is ".$reminderDate.". Wish him a happy birthday.";
                break;
            case '2':
                $responseMsg = "It is ".$reminderDate.". This is your wife's birthday. Do you have a plan?";
                break;
            case '3':
                $responseMsg = "Hi It's the birthday of someone from your family. It is ".$reminderDate.". Wish him/her a happy birthday.";
                break;
            case '4':
                $responseMsg = "It is ".$reminderDate.". This is the birthday of someone you like. Wish him/her a happy birthday.";
                break;
            case '5':
                $responseMsg  = "Hi, it's your friendship anniversary. It is ".$reminderDate.". Congratulations to both of you.";
                break;
            case '6':
                $responseMsg = "Hi, it's your marriage anniversary. It is ".$reminderDate.". Congratulations to both of you.";
                break;
            case '7':
                $responseMsg = "Hi, it's death anniversary of someone you were close to. It is ".$reminderDate.". We're sorry for your loss.";
                break;
            case '8':
                $responseMsg = "Hi, it's an anniversary. It seems like it's important yo you. It is ".$reminderDate.". Celebrate";
                break;
            case '9':
                $responseMsg = "Hi, it was the day you proposed/ have been proposed. Congratulations. It is ".$reminderDate.". ";
                break;
            case '10':
                $responseMsg = "It's ".$reminderDate.". It was the day your first date happened, remember? Celebrate! ";
                break;
        }
    }
    else 
    {
        $responseMsg = "Sorry, you happened to face an error. It'll be fixed in an hour.";
    }

    
    
    /* This will be the Responded Message the app sends out */
    // $responseMsg = "This will be the SMS or this might not be"; 

    function loadSmsSender()
    {
    // Create the sender object server url
    $sender = new SmsSender("http://localhost:7000/sms/send");
    //sending a one message
 	$applicationId = "APP_012301";
 	$encoding = "0";
 	$version =  "1.0";
    $password = "password";
    $sourceAddress = "77000";
    $deliveryStatusRequest = "1";
    $charging_amount = "15.75";
    $destinationAddresses = array($address);
    $binary_header = "";
    $res = $sender->sms($responseMsg, $destinationAddresses, $password, $applicationId, $sourceAddress, $deliveryStatusRequest, $charging_amount, $encoding, $version, $binary_header);
    }
} catch (SmsException $ex) {
    //throws when failed sending or receiving the sms
    error_log("ERROR: {$ex->getStatusCode()} | {$ex->getStatusMessage()}");
}


?>