<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();
// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) {
	header('Location: ../index.php');
	exit;
}
else{
	// Make sure that only administrators can view this page
	if($_SESSION['user_level'] != 3){
		header('Location: node.php');
	}

	// Create DB connection
	include '../actions/connect.php';

	// Register pages
	$pages = array(
		"general",
		"node",
		"account"
	);

	// Sanitize $_GET
	if(!isset($_GET['page'])){
		$page = "general";
	}
	else{
		$page = (in_array($_GET['page'], $pages))? $_GET['page'] : "general";
	}

	// Directory of pages
	switch ($page) {
		case "general":
			include 'content/dashboard/general.php';
			break;
		case "node":
			include 'content/dashboard/node_admin.php';
			break;
		case "account":
			include 'content/dashboard/account_admin.php';
			break;			
		default:
			include 'content/dashboard/general.php';
	}
}
?>
