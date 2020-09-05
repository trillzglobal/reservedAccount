<?php
/*	
{
   "_id":"5f510d996eeae037ebbf2007",
   "organization":{
      "_id":"5f4f8c3880559404aacc6547",
      "contract":"5f4a2ec532a0e37ec2ef6dd0",
      "userId":"5f4f8aebc6478b0067d8c648",
      "name":"Callphone",
      "accountsPrefix":"Callphone",
      "notificationSettings":{
         "_id":"5f4f8c3880559404aacc6548",
         "sendSms":false,
         "sendEmail":false
      },
      "isApproved":true,
      "isDeleted":false,
      "createdAt":"2020-09-02T12:12:40.324Z",
      "updatedAt":"2020-09-03T09:22:19.926Z",
      "__v":0,
      "email":"info@callphoneng.com",
      "phoneNumber":"09038864341",
      "settlementDetails":{
         "_id":"5f4f8d5880559404aacc6550",
         "schedule":"INSTANT",
         "bankCode":"000013",
         "accountName":"CALLPHONELTD",
         "accountNumber":"0150776434"
      },
      "webhookUrl":"https://api.airvendng.net/monifyAgent/payant/webhook/",
      "approvedAt":"2020-09-03T09:22:19.925Z",
      "status":"Activated"
   },
   "account":{
      "_id":"5f510a546eeae037ebbf1ffe",
      "organization":"5f4f8c3880559404aacc6547",
      "customer":{
         "_id":"5f510a546eeae037ebbf1fff",
         "name":"Michael Ojo",
         "email":"trillzglobal@gmail.com",
         "phoneNumber":"",
         "sendNotifications":false
      },
      "splitIncome":false,
      "restrictPaymentSources":false,
      "limitPayments":false,
      "accountName":"Michael Ojo",
      "bankCode":"000001",
      "accountNumber":"8261577197",
      "balance":0,
      "currency":"NGN",
      "country":"NG",
      "type":"RESERVED",
      "isMain":false,
      "status":"ACTIVE",
      "isDeleted":false,
      "createdAt":"2020-09-03T15:23:00.519Z",
      "updatedAt":"2020-09-03T15:23:00.519Z",
      "__v":0
   },
   "counterParty":{
      "_id":"5f510d996eeae037ebbf2008",
      "bankCode":"000012",
      "accountNumber":"0032738646",
      "accountName":"MICHEAL ABAYOMI OJO"
   },
   "paymentReference":"CON|202009031536|059093",
   "transactionReference":"1IY68793C1YDD217",
   "sessionId":"000012200903163650000186071323",
   "narration":"TRF FRM MICHEAL ABAYOMI OJO",
   "type":"Transaction",
   "amount":100,
   "fee":30,
   "vat":2.25,
   "stampDuty":0,
   "currency":"NGN",
   "status":"PAID",
   "settlementStatus":"PENDING",
   "isDeleted":false,
   "createdAt":"2020-09-03T15:36:57.343Z",
   "updatedAt":"2020-09-03T15:36:57.343Z",
   "__v":0
}
*/


require_once("../../functions/variables.php");
include '../../../functions/func_inc.php';
include '../../../functions/db_inc.php';

//Get json request Body
$input= file_get_contents('php://input');

$data = json_decode($input, FALSE);

$transactionReference = $data->_id;
$paymentReference = $data->paymentReference;
$amountPaid = $data->amount;
$totalPayable = $data['totalPayable'];
$settlementAmount = $data->fee;
$paidOn = $data->createdAt;
$paymentStatus = $data->status;
$accountReference = $data->customer->_id;
$paymentDescription = $data['paymentDescription'];
$transactionHash = $data['transactionHash'];
$clientSecret = MNFY_SECRET;


$date           = date('Y m d H:i:s');
    $server         = print_r($_SERVER,1);
    $request        = print_r($_REQUEST,1);

$log_string = "\n***********************************\n\n" .$date."\n"
        . $input ."\n" .
        'SERVER: ' . $server ."\n".$hash."\n".$transactionHash.
        "\n\n";
				
error_log($log_string,3,'vtu2_request.log');


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
//Verify if transaction was from Payant
$confTran = confirmTransactionPayant($transactionReference);
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