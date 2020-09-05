<?php


require_once("../../functions/variables.php");
include '../../../functions/func_inc.php';

include '../../../functions/db_inc.php';

//Get json request Body
$input= file_get_contents('php://input');

$data = json_decode($input, TRUE);

$transactionReference = $data['transactionReference'];
$paymentReference = $data['paymentReference'];
$amountPaid = $data['amountPaid'];
$totalPayable = $data['totalPayable'];
$settlementAmount = $data['settlementAmount'];
$paidOn = $data['paidOn'];
$paymentStatus = $data['paymentStatus'];
$accountReference = $data['product']['reference'];
$paymentDescription = $data['paymentDescription'];
$transactionHash = $data['transactionHash'];
$clientSecret = MNFY_SECRET;

$value = "{$clientSecret}|{$paymentReference}|{$amountPaid}|{$paidOn}|{$transactionReference}";
$hash = hash("sha512",$value);

$date           = date('Y m d H:i:s');
    $server         = print_r($_SERVER,1);
    $request        = print_r($_REQUEST,1);

$log_string = "\n***********************************\n\n" .$date."\n"
        . $input ."\n" .
        'SERVER: ' . $server ."\n".$hash."\n".$transactionHash.
        "\n\n";
				
error_log($log_string,3,'vtu2_request.log');

if($hash != $transactionHash){
     
    exit();
}

//get the APP details and account details for the payer

$appDetails = getApp($accountReference,$mysqli);

/*
 * app Name 
 * app ID
 * webhook
 * charges
 * account Number
 */

$appName = $appDetails['appName'];
$appID = $appDetails['appID'];
$webhook = $appDetails['webhook'];
$charges = $appDetails['charges'];
$hashKey = $appDetails['hashkey'];
$accountNumber = $appDetails['accountNumber'];


				
error_log($log_string,3,'vtu2_request.log');
//Verify if transaction was from Monnify
$confTran = confirmTransaction($transactionReference);
$cp = print_r($confTran,1);


if($confTran == FALSE){
   
    exit();
}


//Verify if Webhook has been treated earlier

$confWebhook = confirmTransactionHook($transactionReference,$mysqli);
if($confWebhook == TRUE){
   
    exit();
}
/*
 * Insert into the Database
 * $transactionReference, $customerReference, $amountPaid, $totalAmount, 
 * $apiCall, $appName, $appID, $appCharges, $amountSent, $accountNumber, $status
 */

$details['transactionReference'] = $transactionReference;
$details['customerReference'] = $accountReference;
$details['amountPaid'] = $amountPaid;
$details['totalAmount'] = $totalPayable;
$details['apiCall'] = $input;
$details['appName'] = $appName;
$details['appID'] = $appID;
$details['appCharges'] = $charges;
$details['amountSent'] = $amountPaid - $charges;
$details['accountNumber'] = $accountNumber;
$details['status'] = "PAID";

walletFunding($details, $mysqli);

$log_string = "\n***********************************\n\n" .$accountNumber."\n"
        . $accountReference ."\n" .
        'SERVER: ' .print_r(mysqli_error($mysqli),1) ."\n".$cp."\n".$appID.
        "\n\n";
error_log($log_string,3,'vtu2_request.log');

$date = date("Y-m-d H:i:s");
/*
 * Call the application
 */
$response = array(
    "transactionReference"=>$transactionReference,
    "customerReference"=>$accountReference,
    "amountPaid"=>$amountPaid,
    "amountSent"=>$details['amountSent'],
    "accountNumber"=>$accountNumber,
    "date"=>$date,
);

callApp($hashKey, $webhook, $response);