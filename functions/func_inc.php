<?php
require_once("variables.php");
//Functions to chech if App Account Exist
function verifyApp($appID,$hash,$mysqli){
$sql = "SELECT * FROM rA_vendors WHERE appID = '{$appID}' AND hashkey = '{$hash}'";
$query = mysqli_query($mysqli,$sql);
$res = mysqli_fetch_assoc($query);
if($res){
    return $res;
}
else{
    return FALSE;
}
}

//Function to Record Account Opening Query
function accountOpening($details,$mysqli){
    $appName = $details['appName'];
    $appID = $details['appID'];
    $customerReference = $details['customerReference'];
    $customerName = $details['customerName'];
    $customerEmail = $details['customerEmail'];
    
           
    $sql ="INSERT INTO rA_vendors_request (appName,appID,dateTime,customerReference,customerName,customerEmail) "
            . "VALUE('$appName','$appID', NOW(), '$customerReference','$customerName','$customerEmail')";
    $query = mysqli_query($mysqli,$sql);
    
  //  print_r($sql);
  //  exit();
    if($query){
        $id = mysqli_insert_id($mysqli);
        return $id;
    }
    else{
        return FALSE;
    }
}

//Function to document Account
function accountDocument($details,$mysqli){
    $appName = $details['appName'];
    $appID = $details['appID'];
    $customerReference = $details['customerReference'];
    $customerName = $details['customerName'];
    $accountNumber = $details['accountNumber'];
    $customerEmail = $details['customerEmail'];
    $status = 1;
    $reservationReference = $details['reservationReference'];
    $apiCall = $details['apiCall'];
    $apiResponse = $details['apiResponse'];

    $sql ="INSERT INTO rA_customer_account (appName,appID,customerReference,customerName,customerEmail,accountNumber,status,dateTime,reservationReference,apiCall,apiResponse) "
            . "VALUE('$appName', '$appID', '$customerReference', '$customerName', '$customerEmail', '$accountNumber', $status, NOW(), '$reservationReference', '$apiCall', '$apiResponse')";
   $query = mysqli_query($mysqli,$sql);

    if($query){
        return TRUE;
    }
    else{
        return FALSE;
    }
}

//Function to verify if CustomerReference is unique
function verifyReference($ref,$mysqli){
    $sql = "SELECT * FROM rA_customer_account WHERE customerReference = '$ref'";
    $query = mysqli_query($mysqli, $sql);
    $resp = mysqli_num_rows($query);
    if($resp >= 1){
        return FALSE;
    }
    else{
        return TRUE;
    }
}

//Get Webhook, App charges for APP 
function getApp($ref,$mysqli){
    $sql = "SELECT appID, accountNumber FROM rA_customer_account WHERE customerReference = '$ref'";
    $query = mysqli_query($mysqli, $sql);
    $num = mysqli_num_rows($query);


    if($num < 1){
        return FALSE;
    }else{
        $row = mysqli_fetch_assoc($query);
        $appID = $row['appID'];
        $accountNum = $row['accountNumber'];
        $sql = "SELECT * FROM monify_vendors WHERE appID = '$appID'";
        $query = mysqli_query($mysqli, $sql);
        $row = mysqli_fetch_assoc($query);
        $row['accountNumber']= $accountNum;
        return $row;
    }
}

//Document wallet Fundung transaction
function walletFunding($details, $mysqli){
    $transactionReference = $details['transactionReference'];
    $customerReference = $details['customerReference'];
    $amountPaid = $details['amountPaid'];
    $totalAmount = $details['totalAmount'];
    $apiCall = $details['apiCall'];
    $appName = $details['appName'];
    $appID = $details['appID'];
    $appCharges = $details['appCharges'];
    $amountSent = $details['amountSent'];
    $accountNumber = $details['accountNumber'];
    $status = 1;
    $sql = "INSERT INTO rA_webhook_transaction (transactionReference, customerReference, amountPaid, totalAmount, dateTime, apiCall,appName, appID, appCharges, amountSent, accountNumber, status)"
            . "VALUE('$transactionReference', '$customerReference', '$amountPaid', '$totalAmount', NOW(), '$apiCall', '$appName', '$appID', '$appCharges', '$amountSent', '$accountNumber', '$status')";
    
    $query = mysqli_query($mysqli, $sql);
    if($query){
        return TRUE;
    }
    else{
        return FALSE;
    }
    
}

function confirmTransactionHook($transactionReference,$mysqli){
    
    $sql = "SELECT * FROM rA_webhook_transaction WHERE transactionReference ='$transactionReference' ";
    $query = mysqli_query($mysqli,$sql);
    $num = mysqli_num_rows($query);
    if($num > 0){
        return TRUE;
    }
    else{
        return FALSE;
    }
}
function monifyLogin(){
    $apiKey = MNFY_APIKEY;
    $secretKey = MNFY_SECRET;
    $baseurl = MNFY_BASEURL;
    $endpoint = "api/v1/auth/login";
    //base64
    $hash = base64_encode("{$apiKey}:{$secretKey}");
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseurl.$endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Basic {$hash}"));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $response = curl_exec($ch);
    curl_close($ch);
    $out = json_decode($response, true);
    $hashKey = $out['responseBody']['accessToken'];
    
    return $hashKey;
}

function payantLogin(){

    $baseurl = PYNT_BASEURL;
    $endpoint = "oauth/token";
    $data = array(
                "username"=>PYNT_USERNAME,
                "password"=>PYNT_PASSWORD);
    $content = json_encode($data);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseurl.$endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $response = curl_exec($ch);
    curl_close($ch);
    $out = json_decode($response, false);
    $hashKey = $out->data->token;
    
    return $hashKey;
}

function confirmTransaction($transRef){
    $apiKey = MNFY_APIKEY;
    $secretKey = MNFY_SECRET;
    $baseurl = MNFY_BASEURL;
    $data =  array("transactionReference" => $transRef);
    $getData = http_build_query($data);
    $endpoint = "api/v1/merchant/transactions/query?".$getData;
    //base64
    $hash = base64_encode("{$apiKey}:{$secretKey}");
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseurl.$endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Basic {$hash}"));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $out = json_decode($response, true);
    $code = $out['responseCode'];
   
    if($code == 0){
        return TRUE;
    }
    else{
        return FALSE;
    } 
}

function confirmTransactionPayant($ref){
    $hash = payantLogin();
    print_r($hash);
    $baseurl = PYNT_BASEURL;
    $organizationId = PYNT_ID;
    $endpoint = "accounts/transactions/".$ref;
   
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseurl.$endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer {$hash}","Content-Type: application/json", "OrganizationID: {$organizationId}"));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $response = curl_exec($ch);
    curl_close($ch);
    $status = json_decode($response, FALSE);
    if($status->statusCode == 200){
        return TRUE;
    }
    else{
        return FALSE;
    }
}

function callApp($appID, $webhook, $response){

    
    $content =  json_encode($response);
    $pre_hash = $content.$appID;
    $hash = hash("sha512",$pre_hash); //Dynamic Hash to confirm transaction is from Airvend Server
    
    
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webhook);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: {$hash}"));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $output = curl_exec($ch);
    curl_close($ch);
    
    return $output;
    
    
}

//To create Account Number for Monnify
function reserveAccount($details){
    $hash = monifyLogin();
    $baseurl = MNFY_BASEURL;
    $endpoint = "api/v1/bank-transfer/reserved-accounts";
    $details['contractCode'] = MNFY_CONCODE;
    $details['currencyCode'] = MNFY_CUCODE;
   
    $content = json_encode($details);
   //print_r($content);
   // exit();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseurl.$endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer {$hash}","Content-Type: application/json"));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

//To create Account Number for Payant
function reserveAccountPayant($details){
    $hash = payantLogin();
    print_r($hash);
    $baseurl = PYNT_BASEURL;
    $organizationId = PYNT_ID;
    $endpoint = "accounts";
    $details['country'] = PYNT_COUNTRY;
    $details['currency'] = PYNT_CUCODE;
    $details['bankCode'] = PYNT_BNKCODE;
    $details['type'] = PYNT_TYPE;
    $details['accountName'] = $details["customer"]["name"];
   
    $content = json_encode($details);
   //print_r($content);
   // exit();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseurl.$endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer {$hash}","Content-Type: application/json", "OrganizationID: {$organizationId}"));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}


 function jsonResponse($response){
     // Define HTTP responses
     $http_response_code = array(
         100 => 'Pending',
         200 => 'OK',
         300 => 'Failed',
         400 => 'Bad Request',
         405 => 'Method Not Allowed',
         500 => 'Internal Server Error'
         );

     // Set HTTP Response
     header('HTTP/1.1 '.$response['status'].' '.$http_response_code[ $response['status'] ]);
     // Set HTTP Response Content Type
     $code = array("confirmationMessage" => $http_response_code[ $response['status'] ],
                    "confirmationCode"=>$response["status"],
                    "details"=>$response);
     $json_response = json_encode($code);
     // Deliver formatted data
     echo $json_response;

     exit();

}