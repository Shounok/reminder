<?php

include_once 'lib/ussd/MoUssdReceiver.php';
include_once 'lib/ussd/MtUssdSender.php';
include_once 'lib/subscription/MoSubscribe.php';
include_once 'log.php';
ini_set('error_log', 'ussd-app-error.txt');
@ob_start();




$receiver = new MoUssdReceiver(); // Create the Receiver object
$receiverSessionId = $receiver->getSessionId();
logFile("recieverSession: ".$receiverSessionId);
$address = $receiver->getAddress(); // get the sender's address
//session_id($receiverSessionId); //Use received session id to create a unique session

$content = $receiver->getMessage(); // get the message content

$requestId = $receiver->getRequestID(); // get the request ID
$applicationId = $receiver->getApplicationId(); // get application ID
$encoding = $receiver->getEncoding(); // get the encoding value
$version = $receiver->getVersion(); // get the version
$sessionId = $receiver->getSessionId(); // get the session ID;
$serviceID;
//session_start();
$_SESSION['menu-Opt']=null;
logFile($SessionId);

$ussdOperation = $receiver->getUssdOperation(); // get the ussd operation
logFile("[ content=$content, address=$address, requestId=$requestId, applicationId=$applicationId, encoding=$encoding, version=$version, sessionId=$sessionId, ussdOperation=$ussdOperation ]");

/*
$appPassword = 'c2f4a01c51b8f74c811f0ea9d7bb50a9';
if($appPassword != '')
{
    $subscription = new MoSubscribe("http://developer.bdapps.com/subscription/getstatus");
    $checkSubscription = $subscription->checkSubscriptionStatus($applicationId,$password,$address);
    
    logFile('The Server is hit and Application subscription status can be cheked.');
    
    //The response should be recieved here.
    $subscriptionStatus = $subscription->getSubscriptionStatus();
    
    if($subscriptionStatus == false)
    {
        //If Not Subscribed
        $subscription = new MoSubscribe("http://developer.bdapps.com/subscription/send");
        $registerUser = $subscription->subscription($applicationId, $appPassword, $version, 1, $address);
        logFile("The server is Hit and the user isn't registered");
    }
    else
    {
        //If Subscribed
    }
} else
{
    logFile("The application isn't configured properly.");
}
*/

// Algorithm
$responseMsg = array(
    "registration" => "Do you want to register as a new user?
    1. Yes
    2. No",
    "main" => "Select a Day
    1. Birth Day
    2. Anniversary
    3. Other Days
    4. Unsubscribe
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
    "birthday_friend" => "Enter your friend's valid birthday in ddmmyyyy format
    000. Exit",
    "birthday_wife" =>  "Enter your wife's valid birthday in ddmmyyyy format
    000. Exit",
    "birthday_family" =>  "Enter your family's valid birthday in ddmmyyyy format
    000. Exit",
    "birthday_others" =>  "Enter other's valid birthday in ddmmyyyy format
    000. Exit",
    "anniversary_friendship" => "Enter valid friendship anniversary in ddmmyyyy format
    000. Exit",
    "anniversary_marriage" => "Enter valid marriage anniversary in ddmmyyyy format
    000. Exit",
    "anniversary_death" => "Enter valid death anniversary in ddmmyyyy format
    000. Exit",
    "anniversary_others" => "Enter other anniversary date in valid ddmmyyyy format
    000. Exit",
    "otherdays_firstdate" => "Enter the date of first date in valid ddmmyyyy format
    000. Exit",
    "otherdays_proposaldate" => "Enter your propsal date in valid ddmmyyyy format
    000. Exit",
    "cancellation" => "Thank you for using our service",
    "unsubscribe" => "Do you really want to unsubscribe from the service?
    1. Yes
    2. No",
    "confirm_unsubscribe" => "You unsubscribed from this service successfully."
   );
logFile("Previous Menu is := " . $_SESSION["menu-Opt"]);            //Get previous menu number

    //Send the Main or Registration menu

if (($receiver->getUssdOperation()) == "mo-init") 
{
    /*
    // Check if msisdn is registered
    if(checkAddress($address))                          
    {                                  
        //loadUssdSender($sessionId, $responseMsg["registration"],$address);
        if (!(isset($_SESSION['menu-Opt']))) {
            $_SESSION['menu-Opt'] = "main"; //Initialize main menu
        }
        loadUssdSender($sessionId, $responseMsg["main"],$address);
    } else {
        //loadUssdSender($sessionId, $responseMsg["main"],$address);
        $_SESSION['menu-Opt'] = "registration"; //Initialize main menu
        loadUssdSender($sessionId, $responseMsg["registration"],$address);
    }
    */

    
    if(!isset($_SESSION['menu-Opt'])){
        $_SESSION['menu-Opt']   = "main";
	logFile("The Program reaches here.");
    }
	else {
	logFile("It sets a menu-Opt.");
	}
    //logFile("The Program reaches here too.");
    //$response = loadUssdSender($sessionId, $responseMsg["main"],$address);
    //logFile($response);
	//logFile("[SessionID=$sessionId, Response=$responseMsg['main'], Address= $address]");
    
if (($receiver->getUssdOperation()) == "mo-cont") 
{
    $menuName = null;
    switch($_SESSION['menu-Opt']) 
    {
        case 'registration':
            switch ($receiver->getMessage()) {
                case '1':
                    $menuName = "main";
                    break;
                case '2':
                    $menuName = "cancellation";
                    break;
            }
            $_SESSION['menu-Opt'] = $menuName;
            break;
        case "main":
            switch ($receiver->getMessage()) {
                case "1":
                    $menuName = "birthday";
                    break;
                case "2":
                    $menuName = "anniversary";
                    break;
                case "3":
                    $menuName = "otherdays";
                    break;
                case '4':
                    $menuName = "unsubscribe";
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
            $_SESSION['service_id'] = $serviceID;
            break;
        case 'unsubscribe':
            switch ($receiver->getMessage()) {
                case '1':
                    deleteSubscriber($receiver->getAddress());
                    $menuName = "confirm_unsubscribe";
                    break;
                case '2':
                    $menuName = "main";
                    break;

            }
            $_SESSION['menu-Opt'] = $menuName;
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
        session_destroy();
    }
    else 
    {
        logFile("Selected response message := " . $responseMsg[$menuName]);
        $response = loadUssdSender($sessionId, $responseMsg[$menuName], $address);
        logFile($response);
        session_destroy();
    }
}


/*
    Get the session id and Response message as parameter
    Create sender object and send ussd with appropriate parameters
**/
function loadUssdSender($sessionId, $responseMessage,$address)
{
    $password = "c2f4a01c51b8f74c811f0ea9d7bb50a9";
    $destinationAddress = $address;
    if ($responseMessage == "000") {
        $ussdOperation = "mt-fin";
    } else {
        $ussdOperation = "mt-cont";
    }
    //$chargingAmount = "0";
    $applicationId = "APP_012915";
    try {
        // Create the sender object server url
        $sender = new MtUssdSender("https://developer.bdapps.com/ussd/send"); // Application ussd-mt sending https url
        $response = $sender->ussd($applicationId, $password, $version, $responseMessage,
            $sessionId, $ussdOperation, $destinationAddress, $encoding, $chargingAmount);
        logFile($response);
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
    $host= '116.193.219.158';
    $db= 'dbussdreminder';
    $dsn = "mysql:host=$host; dbname=$db";
    $db_username = 'xossadmin';
    $db_password = 'Asdf1234'; 
    $subscribed_month = getTotalSubscribedMonth();
    global $sessionId;
    global $address;
    global $reminderDate;
    $serviceID = $_SESSION['service_id'];
    
    $billingStartDate = getBillingStartDate();
   
    try {
        //Insert each request into the table
        $stmt = new PDO($dsn, $db_username, $db_password);
        $sql = "INSERT INTO `tbl_request` ( `msisdn`, `session_id`, `service_id`, `reminder_date`, `billing_date_start`, `subscribed_month_total`, `subscription`) VALUES ('".$address."', '".$sessionId."','".$serviceID."','".$reminderDate."', CURRENT_TIMESTAMP, NULL, '1');";
        $query = $stmt->prepare($sql);
        $query->execute();
        
        //Check if the subscriber already exists, otherwise insert into the table
        $sql2 = "INSERT INTO `tbl_subscribers` (`subscriber_address`, `service_id`)
        SELECT '".$address."', '".$serviceID."' FROM `tbl_subscribers`        
        WHERE NOT EXISTS (
            SELECT `subscriber_address` FROM `tbl_subscribers` WHERE `subscriber_address` = '".$address."'
        ) LIMIT 1";
        $query = $stmt->prepare($sql2);
        $query->execute();
    } catch (PDOException $e) {
        echo "Connection Failed". $e->getMessage();
    }
    
}

function checkAddress($address)
{
    $host = '116.193.219.158';
    $db = 'dbussdreminder';
    $dsn = "mysql:host=$host; dbname=$db";
    $db_username = 'xossadmin';
    $db_password = 'Asdf1234'; 

    try {
        $stmt = new PDO($dsn, $db_username, $db_password);
        $sql = "SELECT `subscriber_address` FROM `tbl_subscribers` WHERE `subscriber_address`= '".$address."'";
        $query = $stmt->prepare($sql);
        $query->execute();
        $address_exists = ($query->rowCount() > 0) ? TRUE : FALSE;
    } catch (PDOException $e) {
        echo "Connection Failed". $e->getMessage();
    }
    return $address_exists;
}
function deleteSubscriber($address)
{
    $dsn = 'mysql:host=localhost; dbname=db_ussdreminder';
    $db_username = 'root';
    $db_password = ''; 
    try {
        $stmt = new PDO($dsn, $db_username, $db_password);
        $sql = "DELETE FROM `tbl_subscribers` WHERE `subscriber_address` = '".$address."'";
        $query = $stmt->prepare($sql);
        $query->execute();
    } catch (\Throwable $th) {
        //throw $th;
    }
}


?>

