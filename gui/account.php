<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();
// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) {
	header('Location: ../index.php');
	exit;
}
else{

	include '../actions/connect.php';

	$pages = array(
		"general",
	);

	if(!isset($_GET['page'])){
		$page = "general";
	}
	else{
		$page = (in_array($_GET['page'], $pages))? $_GET['page'] : "general";
	}

	// Directory
	switch ($page) {
		case "general":
			include 'content/account/general.php';
			break;
		default:
			include 'content/account/general.php';
	}
}
?>