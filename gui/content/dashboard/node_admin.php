<?php

if($_SESSION['user_level'] < 2){
    header('Location: account.php');
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/actions/node_mgmt.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/actions/utility/gui_builder.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/gui/gui_text.php');

$gui = new Gui_Builder();

settype($_GET['id'], "int");    // sanitize value
if(!$_GET['id']){
    header('Location: dashboard.php'); // redirect if value is not a valid int
}

$node_admin = new Node_Management($connection, $_GET['id']);
// echo $node_admin->confirm_node_id();
if(!$node_admin->confirm_node_id()){
    header('Location: dashboard.php'); // redirect if value is not a valid int
}

$alias_error = NULL;
$alias_message = NULL;
$owner_error = NULL;
$owner_message = NULL;
$sharing_error = NULL;
$sharing_message = NULL;

///// Manage Form Actions /////

    // Set Alias
    if(isset($_POST['setNodeAlias']) && $_POST['setNodeAlias'] == session_id()){
        if($node_admin->set_alias($_POST['alias'])){
            $alias_message = "Alias updated";
        }
        else {
            if($error_message == NULL){
                $alias_error = "ERROR: Name includes invalid characters.";
            }
            else{
                $alias_error = $error_message;
            }
        }
    }

    // Clear Alias
    if(isset($_POST['clearAlias']) && $_POST['clearAlias'] == session_id()){
        if($node_admin->get_node_alias()){
            $node_admin->clear_alias();
            $alias_message = "Alias removed";
        }
        else{
            if($error_message == NULL){
                $alias_error = "Alias was not set for this node";
            }
            else{
                $alias_error = $error_message;
            }
        }
    }

    // Set Owner
    if(isset($_POST['setOwner']) && $_POST['setOwner'] == session_id()){
        if(isset($_POST['member_id'])){
            if(count($_POST['member_id']) > 1){
                $owner_error = "Only one user my be set as a node owner";
            }
            else{
                if($node_admin->transfer_owner($_POST['member_id'][0])){
                    $owner_message = "Ownership transferred";
                }
            }
        }
    }

    // Change Access
    if(isset($_POST['changeAccess']) && $_POST['changeAccess'] == session_id()){
        if(isset($_POST['access_account'])){
            if(count($_POST['access_account']) > 1){
                $owner_error = "Only select one user at a time";
            }
            if(isset($_POST['user_level'])){
                settype($_POST['user_level'], "int");
                settype($_POST['access_account'][0], "int");
                if($_POST['user_level'] >= 1 && $_POST['user_level'] <= 3){
                    if($node_admin->set_user_level($_POST['user_level'], $_POST['access_account'][0])){
                        $owner_message = "User-level updated";
                    }
                    else{
                        if($error_message == NULL){
                            $owner_error = "User-level could not be updated";
                        }
                        else{
                            $owner_error = $error_message;
                        }
                    }
                }
                else{
                    $owner_message = "Error: Invalid user-level value";
                }
            }
        }
    }
// Test 
// $node_admin->printArray($_POST);


?>

<html lang="en" dir="ltr">
	<head>
		<meta charset="utf-8">
		<title>Admin Dashboard</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" type="text/css" href="/assets/css/dashboard.css">
		<link rel="stylesheet" href="/assets/fonts/font-awesome-5.14.0/css/all.css">
		<script src="/assets/js/management.js"></script>

	</head>

	<body>
    	<nav>
			<input type="checkbox" id="check">
			<label for="check" class="checkbtn">
				<i class="fas fa-bars"></i>
			</label>
			<name><label class="logo"><a href="dashboard.php?page=general" class="back-arrow"><i class="fas fa-arrow-circle-left"></i></a><span class="logo-label">Node Manager</span></label></name>
			<?php include 'nav_menu.php'; ?>
        </nav>
        

<section class="container">
<!-- Content Title -->
<?php $title = ($node_admin->get_node_alias() != NULL) ? $node_admin->get_node_alias() : $node_admin->get_node_name(); ?>
<?php echo $gui->page_content_title("Node: ".$title); ?>

<div style="overflow:auto">
  <div class="page-menu">
    <a href="dashboard.php?page=general" class="root-page"><i class="fas fa-chevron-left"></i> General</a>
    <a href="#"><i class="fas fa-chevron-down"></i> Node</a>
    <a href="#" style="padding-left: 25px;">Alias</a>
    <a href="#" style="padding-left: 25px;">Clients</a>
    <a href="#" style="padding-left: 25px;">Sharing</a>
  </div>

	<div class="main">

        <section>
            <!-- Set Alias -->
            <div class="content">
                <?php echo $gui->section_heading("far fa-address-book", $node_alias_title, $node_alias_tooltip); ?>
                <?php echo $gui->section_message($alias_error, $alias_message); ?>

                <div class="config-item">
                <form method="post" id="manage-node-alias" name="manage-node-alias" action="dashboard.php?page=node&id=<?php echo $node_admin->get_node_id(); ?>">
                <?php  echo ($node_admin->get_node_alias()) ? $node_admin->get_node_alias() : "No alias set"; ?>
                <input type="hidden" id="clearAlias" name="clearAlias" value="<?= session_id(); ?>">
                </form>
                </div>

                <!-- Node Alias Form -->
                <div class="config-form" id="node-alias-form" style="display: none;">
                    <form method="post" id="set-node-alias" name="set-node-alias" action="dashboard.php?page=node&id=<?php echo $node_admin->get_node_id(); ?>">
                    <input type="text" name="alias" id="alias" placeholder="Node Alias">
                    <input type="hidden" id="setNodeAlias" name="setNodeAlias" value="<?= session_id(); ?>">
				<button class="form-button" id="form-button" type="button" onclick="document.getElementById('set-node-alias').submit(); return false;"> Set Alias </button>
                </form>
                </div>

                <!-- Action Buttons -->
                <div class="actions">
                <a class="node-add" onclick='showForm("node-alias-form")'><i class="fas fa-plus-circle" aria-hidden="true"></i><label>Set</label></a>
                <a href="javascript:{}" onclick="document.getElementById('manage-node-alias').submit(); return false;"><i class="fas fa-minus-circle" aria-hidden="true"></i><label>Clear</label></a>
                </div>

            </div>
        </section>


        <section>
            <!-- Set Owner -->
            <div class="content">
                <?php echo $gui->section_heading("fas fa-sitemap", $node_clients_title, $node_clients_tooltip); ?>
                <?php echo $gui->section_message($owner_error, $owner_message); ?>

                <div class="config-item">
                    <!-- Node Owner Form -->
                    <form method="post" id="set-node-owner" name="set-node-owner" action="dashboard.php?page=node&id=<?php echo $node_admin->get_node_id(); ?>">   
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Owner</th>
                                <th>Access</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            foreach($node_admin->node_clients as $id => $client_names){
                                echo "<tr><td><input type=\"checkbox\" name=\"member_id[]\" id=\"user-".$id."\" value=\"".$id."\" onclick=\"toggleCheckbox(this, 'member".$id."')\">
                                <label for=\"user-".$id."\" class=\"table-list-label\">".$client_names[0]['first_name']." ".$client_names[0]['last_name']."</label></td>";
                                echo ($id == $node_admin->node_owner_id)? "<td class=\"table-owner-flag\">Owner</td>": "<td></td>";
                                echo "<td>".$node_admin->get_user_level_name($id)."</td></tr>";
                            }
                        ?>
                    </table>
                    <input type="hidden" id="setOwner" name="setOwner" value="<?= session_id(); ?>">
                    </form>
                </div>

                <!-- Form: Change Access -->
                <div class="config-form" id="change-access-form" style="display: none;">
                <form action="dashboard.php?page=node&id=<?php echo $node_admin->get_node_id(); ?>" method="post" id="change_access" name="change_access">
                <?php
                    echo "<div style=\"display: none;\">";
                    foreach($node_admin->node_clients as $id => $client_names){
                        echo "
                        <input class=\"checkbox-container\" id=\"member".$id."\" type=\"checkbox\" name=\"access_account[]\" value=\"".$id."\">
                        ";
                    }
                    echo "</div>";
                ?>
                    <input class="radio-button" type="radio" id="user" name="user_level" value="1">
                    <label class="radio-button-label" for="user">User</label>
                    <input class="radio-button" type="radio" id="node" name="user_level" value="2" checked>
                    <label class="radio-button-label" for="node">Node Admin</label>
                    <input class="radio-button" type="radio" id="server" name="user_level" value="3">
                    <label class="radio-button-label" for="server">Server Admin</label>
                    
                    <input type="hidden" id="changeAccess" name="changeAccess" value="<?= session_id(); ?>">
                    <button class="form-button" id="form-button" type="button" onclick="document.getElementById('change_access').submit(); return false;"> Save Changes </button>
                </form>
                </div>

                <!-- Action Buttons -->
                <div class="actions">
                <a href="javascript:{}" onclick="document.getElementById('set-node-owner').submit(); return false;"><i class="fas fa-people-arrows" aria-hidden="true"></i><label>Make Owner</label></a>
                <a href="javascript:{}" onclick='showForm("change-access-form")'><i class="fas fa-user-cog" aria-hidden="true"></i><label>Change Access</label></a>
                <a href="javascript:{}" onclick="document.getElementById('set-node-owner').submit(); return false;"><i class="fas fa-user-plus" aria-hidden="true"></i><label>Invite</label></a>
                </div>

            </div>
        </section>


        <section>
            <!-- Set Sharing Parameters -->
            <div class="content">
                <?php echo $gui->section_heading("fas fa-share-alt", $node_sharing_title, $node_sharing_tooltip); ?>
                <?php echo $gui->section_message($sharing_error, $sharing_message); ?>

                <div class="config-item">
                Not yet implemented
                </div>

                <!-- Pop-up Form -->
                <div class="config-form" id="node-form" style="display: none;">
                    <input type="text" name="username" id="username" placeholder="Node Name">
                    <button id="form-button" type="submit"> Add </button>
                </div>

                <!-- Action Buttons -->
                <div class="actions">
                <a class="node-add" onclick='showForm("node-form")'><i class="fas fa-plus-circle" aria-hidden="true"></i><label>Action 1</label></a>
                <a href="javascript:{}" onclick="document.getElementById('manage_node').submit(); return false;"><i class="fas fa-minus-circle" aria-hidden="true"></i><label>Action 2</label></a>
                </div>

            </div>
        </section>

	</div>
</div>

</section>

</body>
</html>           