<?php

require("functions/func_inc.php");
//Test
//echo "NOT YOUR REGULAR DROID... ROBOT ATTACK \n";
//exit();


//Email
//Name
//reference

$ref = uniqid();
$email = "trillzglobal@gmail.com";
$name = "Michael Ojo";
$phone = "";

$det = array(
			"name" => $name,
			"email" => $email,
			"phoneNumber" => $phone,
			"sendNotifications"=>false);

$details = array(
			"customer"=>$det);


//$output = reserveAccountPayant($details);

$transaction = "5f510d996eeae037ebbf2007";
$try = confirmTransactionPayant($transaction);
print_r($try);
