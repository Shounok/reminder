<?php

include_once 'lib/ussd/MoUssdReceiver.php';
include_once 'lib/ussd/MtUssdSender.php';
include_once 'log.php';
ini_set('error_log', 'ussd-app-error.txt');
@ob_start();
$receiver = new MoUssdReceiver(); // Create the Receiver object
$receiverSessionId = $receiver->getSessionId();
session_id($receiverSessionId); //Use received session id to create a unique session
session_start();

$content = $receiver->getMessage(); // get the message content
$address = $receiver->getAddress(); // get the sender's address
$requestId = $receiver->getRequestID(); // get the request ID
$applicationId = $receiver->getApplicationId(); // get application ID
$encoding = $receiver->getEncoding(); // get the encoding value
$version = $receiver->getVersion(); // get the version
$sessionId = $receiver->getSessionId(); // get the session ID;
$ussdOperation = $receiver->getUssdOperation(); // get the ussd operation
logFile("[ content=$content, address=$address, requestId=$requestId, applicationId=$applicationId, encoding=$encoding, version=$version, sessionId=$sessionId, ussdOperation=$ussdOperation ]");

// Algorithm
$responseMsg = array(
    "main" => "Select a Day
    1. Birth Day
    2. Anniversary
    3. Other Days
	000.Exit",
    "birthday" => "Choose Birthday of
	1. Friend
	2. Wife
	3. Family
	4. Others
	999. Back",
    "anniversary" => "Choose the day for
	1. Friendship
    2. Marriage
    3. Death
    4. Others
	999.Back",
    "otherdays" => "Choose the date for
	1. First Date
	2. Proposal Date
	999.Back",
   );
logFile("Previous Menu is := " . $_SESSION["menu-Opt"]); //Get previous menu number
if (($receiver->getUssdOperation()) == "mo-init") { //Send the main menu
    loadUssdSender($sessionId, $responseMsg["main"],$address);
    if (!(isset($_SESSION['menu-Opt']))) {
        $_SESSION['menu-Opt'] = "main"; //Initialize main menu
    }
}
if (($receiver->getUssdOperation()) == "mo-cont") {
    $menuName = null;
    switch ($_SESSION['menu-Opt']) {
        case "main":
            switch ($content) {
                case "1":
                    $menuName = "birthday";
                    break;
                case "2":
                    $menuName = "anniversary";
                    break;
                case "3":
                    $menuName = "otherdays";
                    break;
                default:
                    $menuName = "main";
                    break;
            }
            $_SESSION['menu-Opt'] = $menuName; //Assign session menu name
            break;
        case "birthday":
            $_SESSION['menu-Opt'] = "birthday_list"; //Set to ngo menu back
            switch ($receiver->getMessage()) {
                case "1":
                    $menuName = "birthday_friend";
                    break;
                case "2":
                    $menuName = "birthday_wife";
                    break;
                case "3":
                    $menuName = "birthday_family";
                    break;
                case "4":
                    $menuName = "birthday_others";
                    break;
                case "999":
                    $menuName = "main";
                    $_SESSION['menu-Opt'] = "main";
                    break;
                default:
                    $menuName = "main";
                    break;
            }
            break;
        case "anniversary":
            $_SESSION['menu-Opt'] = "anniversary_list"; //Set to product menu back
            switch ($receiver->getMessage()) {
                case "1":
                    $menuName = "anniversary_friendship";
                    break;
                case "2":
                    $menuName = "anniversary_marriage";
                    break;
                case "3":
                    $menuName = "anniversary_death";
                    break;
                case "4":
                    $menuName = "anniversary_others";
                    break;
                case "999":
                    $menuName = "main";
                    $_SESSION['menu-Opt'] = "main";
                    break;
                default:
                    $menuName = "main";
                    break;
            }
            break;
        case "otherdays":
            $_SESSION['menu-Opt'] = "otherdays_list"; //Set to career menu back
            switch ($receiver->getMessage()) {
                case "1":
                    $menuName = "otherdays_firstdate";
                    break;
                case "2":
                    $menuName = "otherdays_proposaldate";
                    break;
                case "999":
                    $menuName = "main";
                    $_SESSION['menu-Opt'] = "main";
                    break;
                default:
                    $menuName = "main";
                    break;
            }
            break;
        case "birthday_list" || "anniversary_list" || "otherdays_list":
            switch ($_SESSION['menu-Opt']) { //Execute menu back sessions
                case "birthday_list":
                    $menuName = "birthday";
                    break;
                case "anniversary_list":
                    $menuName = "anniversary";
                    break;
                case "otherdays_list":
                    $menuName = "otherdays";
                    break;
            }
            $_SESSION['menu-Opt'] = $menuName; //Assign previous session menu name
            break;
    }
    if ($receiver->getMessage() == "000") {
        $responseExitMsg = "Exit Program!";
        $response = loadUssdSender($sessionId, $responseExitMsg,$address);
        session_destroy();
    } else {
        logFile("Selected response message := " . $responseMsg[$menuName]);
        $response = loadUssdSender($sessionId, $responseMsg[$menuName], $address);
    }
}
/*
    Get the session id and Response message as parameter
    Create sender object and send ussd with appropriate parameters
**/
function loadUssdSender($sessionId, $responseMessage,$address)
{
    $password = "1234";
    $destinationAddress = $address;
    if ($responseMessage == "000") {
        $ussdOperation = "mt-fin";
    } else {
        $ussdOperation = "mt-cont";
    }
    $chargingAmount = "5.00";
    $applicationId = "APP_012301";
    $encoding = "440";
    $version = "1.0";
    try {
        // Create the sender object server url
        $sender = new MtUssdSender("https://localhost:10001/reminder/reminder.php"); // Application ussd-mt sending https url
        $response = $sender->ussd($applicationId, $password, $version, $responseMessage,
            $sessionId, $ussdOperation, $destinationAddress, $encoding, $chargingAmount);
        return $response;
    } catch (UssdException $ex) {
        //throws when failed sending or receiving the ussd
        error_log("USSD ERROR: {$ex->getStatusCode()} | {$ex->getStatusMessage()}");
        return null;
    }
}
/*
function processAddress($address)
{
   
    return str_split($address,4)[1];
}
*/
function insertRequest()
{
    $dsn = 'mysql:dbname=dbussdreminder; host=127.0.0.1';
    $db_username = 'xossadmin';
    $db_password = 'Asdf1234'; 
   /* 
    try {
        $stmt = new PDO($dsn, $db_username, $db_password);
        $sql = "INSERT INTO `tbl_request`(`request_id`, `msisdn`, `session_id`, `service_id`, `request_date`, `target_name`, `target_phoneNumber`, `billing_date_start`, `billing_date_end`, `subscribed_month_total`, `subscription`, `billing_grace`) 
    VALUES (".$requestId.",".$address.",".$sessionId.",[value-4],[value-5],[value-6],[value-7],[value-8],[value-9],[value-10],[value-11],[value-12])";
        $insertData = $stmt->query($sql);
    } catch (PDOException $e) {
        echo "Connection Failed". $e->getMessage();
    }
    */

}

?>