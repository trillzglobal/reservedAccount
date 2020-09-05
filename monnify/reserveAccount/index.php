<?php

//echo 'Creating Account Is suspended at the moment';
//exit();
require_once("../../functions/variables.php");
include "../../functions/db_inc.php";
include "../../functions/func_inc.php";

error_reporting(~E_ALL);
ini_set('display_errors', 1);  

//Get header to collect hash key
$query_st = getallheaders();
$hash = $query_st['hashKey'];
$appID = $query_st['privateID'];

if($_SERVER['REQUEST_METHOD'] != "POST"){
    //Kill Request
    $response['status']= 405;
    jsonResponse($response);
}


//Get json request Body
$input= file_get_contents('php://input');
/*
{accountReference, accountName, customerEmail}
*/
$data = json_decode($input, TRUE);

//Call Application Details
$appVerify = verifyApp($appID,$hash,$mysqli);

//

if(!is_array($appVerify)){
    $response['status'] = 400;
    $response['message'] = "Application Is not Registered or does not exist";
    jsonResponse($response);
}

$appName = $appVerify['appName'];

$accountReference = $data->accountReference;
$accountName = $data->accountName;
$customerEmail = $data->customerEmail;

if(empty($accountReference)){
    $response['status'] = 400;
    $response['message'] = "Account Reference must Not Be Empty";
    jsonResponse($response);
}

if(empty($accountName)){
    $response['status'] = 400;
    $response['message'] = "Account Name must Not Be Empty";
    jsonResponse($response);
}

if(empty($customerEmail)){
    $response['status'] = 400;
    $response['message'] = "Customer Email must Not Be Empty";
    jsonResponse($response);
}

//Verify if accountReference exist
$check = verifyReference($accountReference, $mysqli); 

if($check == FALSE){
    //Kill Because Reference is not Unique
    $response['status'] = 400;
    $response['message'] = "Reference already exist";
    
    jsonResponse($response);
}





//Insert to data table application Request
$details->appName = $appName;
$details->appID = $appID;
$details->customerReference = $accountReference;
$details->customerName = $accountName;
$details->customerEmail = $customerEmail;

accountOpening($details,$mysqli);

//Call API to reserve account
 /*   
    {
    "accountReference": "abc123",
    "accountName": "Test Reserved Account",
    "currencyCode": "NGN",
    "contractCode": "8389328412",
    "customerEmail": "test@tester.com",

}
  */

$detailing = array(
    "accountReference"=>$accountReference,
    "accountName"=>$accountName,
    "customerEmail"=>$customerEmail
);
$response = reserveAccount($detailing);

$output = json_decode($response, TRUE);

if($output->responseCode === "0"){
    
$details->accountNumber = $output->responseBody->accountNumber;
$details->reservationReference = $output->responseBody->reservationReference;
$details->customerReference = $output->responseBody->accountReference;
$details->apiCall = $input;
$details->apiResponse = $response;


$end = accountDocument($details,$mysqli);


//API Response to APP
    $final_response['status'] = 200;
    $final_response['message'] = "Account Number reserved Successfully";
//$final_response['transactionReference']= $transactionReference;
    $final_response['reservationReference']= $details['reservationReference'];
    $final_response['customerReference'] = $details['customerReference'];
//$final_response['amountPaid'] = $amountPaid;
//$final_response['totalAmount'] = $totalAmount;
//$final_response['apiCall'] =  $apiCall;
    $final_response['appName'] =  $appName;
    $final_response['appID'] =  $appID;
//$final_response['appCharges']= $appCharges;
//$final_response['amountSent'] = $amountSent;
    $final_response['accountNumber'] =    $details['accountNumber'];
//$final_response['status']= $status;

    jsonResponse($final_response);

}
else{
    $res['status'] = 300;
    $res['message'] = $output['responseMessage'];
    jsonResponse($res);        
}
