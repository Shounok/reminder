<?php

include_once 'lib/ussd/MoUssdReceiver.php';
include_once 'lib/ussd/MtUssdSender.php';
include_once 'log.php';
ini_set('error_log', 'ussd-app-error.txt');
@ob_start();




$receiver = new MoUssdReceiver(); // Create the Receiver object
$receiverSessionId = $receiver->getSessionId();
$address = $receiver->getAddress(); // get the sender's address
session_id($receiverSessionId); //Use received session id to create a unique session
session_start();
$content = $receiver->getMessage(); // get the message content

$requestId = $receiver->getRequestID(); // get the request ID
$applicationId = $receiver->getApplicationId(); // get application ID
$encoding = $receiver->getEncoding(); // get the encoding value
$version = $receiver->getVersion(); // get the version
$sessionId = $receiver->getSessionId(); // get the session ID;

$serviceID;


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
    "birthday_friend" => "Enter your friend's birthday in ddmmyyyy format
    999. Back",
    "birthday_wife" =>  "Enter your wife's birthday in ddmmyyyy format
    999. Back",
    "birthday_family" =>  "Enter your family's birthday in ddmmyyyy format
    999. Back",
    "birthday_others" =>  "Enter other's birthday in ddmmyyyy format
    999. Back",
    "anniversary_friendship" => "Enter friendship anniversary in ddmmyyyy format
    999. Back",
    "anniversary_marriage" => "Enter marriage anniversary in ddmmyyyy format
    999. Back",
    "anniversary_death" => "Enter death anniversary in ddmmyyyy format
    999. Back",
    "anniversary_others" => "Enter other anniversary date in ddmmyyyy format
    999. Back",
    "otherdays_firstdate" => "Enter the date of first date in ddmmyyyy format
    999. Back",
    "otherdays_proposaldate" => "Enter your propsal date in ddmmyyyy format
    999. Back"
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
    switch($_SESSION['menu-Opt']) {
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
            $_SESSION['service_id'] = $serviceID;
            break;
        case "birthday":
            $_SESSION['menu-Opt'] = "birthday_list"; //Set to  menu 'birthday' back
            switch ($receiver->getMessage()) {
                case "1":
                    $menuName = "birthday_friend";
                    $serviceID = 1;
                    break;
                case "2":
                    $menuName = "birthday_wife";
                    $serviceID = 2;
                    break;
                case "3":
                    $menuName = "birthday_family";
                    $serviceID = 3;
                    break;
                case "4":
                    $menuName = "birthday_others";
                    $serviceID = 4;
                    break;
                case "999":
                    $menuName = "main";
                    $_SESSION['menu-Opt'] = "main";
                    break;
                default:
                    $menuName = "main";
                    break;
            }
            $_SESSION['menu-Opt'] = $menuName; //Assign session menu name
            $_SESSION['service_id'] = $serviceID;
            break;
        case "anniversary":
            $_SESSION['menu-Opt'] = "anniversary_list"; //Set to menu 'anniversary' back
            switch ($receiver->getMessage()) {
                case "1":
                    $menuName = "anniversary_friendship";
                    $serviceID = 5;
                    break;
                case "2":
                    $menuName = "anniversary_marriage";
                    $serviceID = 6;
                    break;
                case "3":
                    $menuName = "anniversary_death";
                    $serviceID = 7;
                    break;
                case "4":
                    $menuName = "anniversary_others";
                    $serviceID = 8;
                    break;
                case "999":
                    $menuName = "main";
                    $_SESSION['menu-Opt'] = "main";
                    break;
                default:
                    $menuName = "main";
                    break;
            }
            $_SESSION['menu-Opt'] = $menuName; //Assign session menu name
            $_SESSION['service_id'] = $serviceID;
            break;
        case "otherdays":
            $_SESSION['menu-Opt'] = "otherdays_list"; //Set to  menu 'otherdays' back
            switch ($receiver->getMessage()) {
                case "1":
                    $menuName = "otherdays_firstdate";
                    $serviceID = 9;
                    break;
                case "2":
                    $menuName = "otherdays_proposaldate";
                    $serviceID = 10;
                    break;
                case "999":
                    $menuName = "main";
                    $_SESSION['menu-Opt'] = "main";
                    break;
                default:
                    $menuName = "main";
                    break;
            }
            $_SESSION['menu-Opt'] = $menuName; //Assign session menu name
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
    //var_dump($receiver->getMessage());
    if ($receiver->getMessage() == "000") 
    {
        $responseExitMsg = "Exit Program!";
        $response = loadUssdSender($sessionId, $responseExitMsg,$address);
        session_destroy();
    } 
    else if(strlen($receiver->getMessage()) == 8)
    {
        $reminderDate = takeReminderDate($receiver->getMessage());
        insertRequest();
        logFile("Data is saved successfully.");
        //$response = loadUssdSender($sessionId, "Your Reminder is saved successfully and your service id is ".$_SESSION['service_id']."", $address);
        $response = loadUssdSender($sessionId, "Your Reminder is saved successfully", $address);
    }
    else 
    {
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
        $sender = new MtUssdSender("http://localhost:7000/ussd/send"); // Application ussd-mt sending https url
        $response = $sender->ussd($applicationId, $password, $version, $responseMessage,
            $sessionId, $ussdOperation, $destinationAddress, $encoding, $chargingAmount);
        return $response;
    } catch (UssdException $ex) {
        //throws when failed sending or receiving the ussd
        error_log("USSD ERROR: {$ex->getStatusCode()} | {$ex->getStatusMessage()}");
        return null;
    }
}

function takeReminderDate($date){
    $dateArray = trim($date);
    if(strlen($dateArray)==8)
    {
        $dateArray = str_split($dateArray,4);
        $dayMonthArray = str_split($dateArray[0],2);

        $year = $dateArray[1];
        $day = $dayMonthArray[0];
        $month = $dayMonthArray[1];
    }
    return "".$year."-".$month."-".$day;
}

function getBillingStartDate()
{
    $currentDate = date('Y-m-d');
    return $currentDate;
}
function getBillingEndDate() 
{
    //$currentDate = date('Y-m-d');
    //return $currentDate;
}
function getTotalSubscribedMonth()
{
    $date1 = getBillingStartDate();
    $date2 = getBillingEndDate();

    $timestamp1 = strtotime($date1);
    $timestamp2 = strtotime($date2);

    $year1 = date('Y', $timestamp1);
    $year2 = date('Y', $timestamp2);
    $month1 = date('m', $timestamp1);
    $month2 = date('m', $timestamp2);

    $month_difference = (($year2 - $year1) * 12) + ($month2 - $month1);
    return $month_difference;
}

function insertRequest()
{
    
    $dsn = 'mysql:host=localhost; dbname=db_ussdreminder';
    $db_username = 'root';
    $db_password = ''; 
    $subscribed_month = getTotalSubscribedMonth();
    global $sessionId;
    global $address;
    global $reminderDate;
    $serviceID = $_SESSION['service_id'];
    
    $billingStartDate = getBillingStartDate();
   
    try {
        $stmt = new PDO($dsn, $db_username, $db_password);
        //$sql = "INSERT INTO `tbl_request` ( `msisdn`, `session_id`, `reminder_date`, `billing_date_start`, `subscribed_month_total`, `subscription`) VALUES ('".$address."', '".$sessionId."', '".$reminderDate."', CURRENT_TIMESTAMP, NULL, '1');";
        $sql = "INSERT INTO `tbl_request` ( `msisdn`, `session_id`, `service_id`, `reminder_date`, `billing_date_start`, `subscribed_month_total`, `subscription`) VALUES ('".$address."', '".$sessionId."','".$serviceID."','".$reminderDate."', CURRENT_TIMESTAMP, NULL, '1');";
        $stmt = $stmt->prepare($sql);
        $stmt->execute();
    } catch (PDOException $e) {
        echo "Connection Failed". $e->getMessage();
    }
    
}


?>

