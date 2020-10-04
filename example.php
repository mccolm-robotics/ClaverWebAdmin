<?php

// include the class
require_once 'totp.class.php';

// for PHP < 5.1.2 use this version
//require_once 'otp_p512.class.php';

/////////////////////////
// generate client code//
/////////////////////////
// create the object
$clientCode = new TOTP();
// set the shared key
$clientCode->setSecretKey("01234567890123456789");

// Set the expiration time (in seconds)
// The expiration time varies between -50% and +50% of the number of sends entered
// So for example if the expiration time is 30,
// the actual expiration time is somewhere between 15 and 45 seconds (at "random")
$clientCode->setExpirationTime(10); // will expire in 10 seconds (default is 30)

// the number of digits the code should have (default to 7)
$clientCode->setDigitsNumber(10);

// Set if the generated code should contain or not a checksum at the end
// If you set it to true do not forget to set it to true on the server code too
//$clientCode->addChecksum(true); // default is false

// generate the code
$origCode = $clientCode->generateCode();

print "<pre>";
ob_start();
// get the timestamp used for generating the code
print "Original time : ".$clientCode->getTimeUsedInGeneration()."\n";
// show the generated code
print "Original password : ".$origCode."\n";
print "Expiration time: ".$clientCode->getExpirationTime()."s\n\n";

/////////////////////////
// generate server code//
/////////////////////////
// testing the validation of the code in time
$secondToTest = 60;
for ($i=0;$i<=$secondToTest;$i++) { // test for 60 seconds
	$serverCode = new TOTP();
	// set the secret key
	$serverCode->setSecretKey("01234567890123456789");
	// set the expiration time (in seconds)
	$serverCode->setExpirationTime(10);
	// the number of digits the code should have (default to 7)
	$serverCode->setDigitsNumber(10);
	
//	$serverCode->addChecksum(true); // default is false
	// verify the client code if is still valid 
	if ($serverCode->validateCode($origCode)) {
		$verif = "true (after ".($serverCode->getTimeUsedInGeneration()-$clientCode->getTimeUsedInGeneration())." seconds)";
		print $serverCode->getTimeUsedInGeneration()."| Code:".$origCode." -> ".$verif."\n";
	} else {
		$verif  = "false (after ".($serverCode->getTimeUsedInGeneration()-$clientCode->getTimeUsedInGeneration())." seconds)";
		$verif .= " (".$serverCode->getGeneratedCode().")";
		print $serverCode->getTimeUsedInGeneration()."| Code:".$origCode." -> ".$verif."\n";
		print "Code valid for: ".$serverCode->getExpirationTime()." (Error: ". ($i-$serverCode->getExpirationTime())*100/$serverCode->getExpirationTime()." %) \n";
		break;
	}
	
	ob_flush();
	flush();
	sleep(1);// wait for 1 second
}

?>