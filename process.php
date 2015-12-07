<?php

include dirname(__FILE__)."/usaepay.php"; 	// Change this path to the location you have save usaepay.php
 
$tran=new umTransaction;
 
$tran->key="H1qyx3Oe8RP68wumRFJ8Pn3PO2DNq24v"; 		// Your Source Key
$tran->pin="1234";		// Source Key Pin
$tran->usesandbox=true;		// Sandbox true/false
$tran->ip=$REMOTE_ADDR;   // This allows fraud blocking on the customers ip address 
$tran->testmode=0;    // Change this to 0 for the transaction to process
 
$tran->command="cc:sale";    // Command to run; Possible values are: cc:sale, cc:authonly, cc:capture, cc:credit, cc:postauth, check:sale, check:credit, void, void:release, refund, creditvoid and cc:save. Default is cc:sale. 
 
$tran->card=$_POST['card_number'];		// card number, no dashes, no spaces
$tran->exp=str_replace('/', '', $_POST['expiration_date']);			// expiration date 4 digits no /
$tran->amount=$_POST['order_number'];			// charge amount in dollars
$tran->invoice=$_POST['transaction_uuid'];   		// invoice number.  must be unique.
$tran->cardholder=$_POST['billing_name']; 	// name of card holder
$tran->street=$_POST['billing_address_1'].', '.$_POST['billing_address_2'];	// street address
$tran->zip=$_POST['billing_zip'];			// zip code
$tran->description="Via Paydunk";	// description of charge
$tran->cvv2=$_POST['cvv'];			// cvv2 code	
 
echo "<h1>Please wait one moment while we process your card...<br>\n";
flush();
 
if($tran->Process())
{
	echo "<b>Card Approved</b><br>";
	echo "<b>Authcode:</b> " . $tran->authcode . "<br>";
	echo "<b>RefNum:</b> " . $tran->refnum . "<br>";
	echo "<b>AVS Result:</b> " . $tran->avs_result . "<br>";
	echo "<b>Cvv2 Result:</b> " . $tran->cvv2_result . "<br>";
	$status = 'success';	
} else {
	echo "<b>Card Declined</b> (" . $tran->result . ")<br>";
	echo "<b>Reason:</b> " . $tran->error . "<br>";	
	if(@$tran->curlerror) echo "<b>Curl Error:</b> " . $tran->curlerror . "<br>";	
	$status = 'error';		
}		

//set data for PUT request
$bodyparams = array(
			"client_id" => "pMSbMB2b9WqPiv2FILcIbeuJW20E7xLLKS5SAaRh", // your APP ID goes here!!!
			"client_secret" => "JlAxg0NynNJlnhvngMnto9n8WvmKIaYWocSchiYG", // your APP SECRET goes here!!!
			"status" => $status);
//sends the PUT request to the Paydunk API
function CallAPI($method, $url, $data = false){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_PUT, 1);		
		$update_json = json_encode($data);	
		curl_setopt($curl, CURLOPT_URL, $url . "?" . http_build_query($data));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSLVERSION, 4);
		$result = curl_exec($curl);  
		$api_response_info = curl_getinfo($curl);
		curl_close($curl);
		return $result;
}
//get the transaction_uuid from Paydunk & call the the Paydunk API
$transaction_uuid = $_POST['transaction_uuid'];
if (isset($transaction_uuid)) {
	$url = "https://api.paydunk.com/api/v1/transactions/".$transaction_uuid;
	CallAPI("PUT", $url, $bodyparams);	
}
?>
