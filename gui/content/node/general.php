<?php

if($_SESSION['user_level'] < 2){
    header('Location: account.php');
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/actions/node_mgmt.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/actions/utility/gui_builder.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/actions/utility/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/gui/gui_text.php');

$gui = new Gui_Builder();
$node_admin = new Node_Management($connection, NULL);

$alias_error = NULL;
$alias_message = NULL;
$owner_error = NULL;
$owner_message = NULL;
$sharing_error = NULL;
$sharing_message = NULL;
$devices_error = NULL;
$devices_message = NULL;

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

    // Remove Device
    if(isset($_POST['manageDevices']) && $_POST['manageDevices'] == session_id()){
        if(isset($_POST['device_id'])){
            foreach($_POST['device_id'] as $id){
                if($node_admin->delete_node_device($id)){
                    $devices_message = "Device deleted.";
                }
                else{
                    $devices_error = "Error deleting device. ".$GLOBALS['error_message'];
                }
            }
        }
    }

    // Create Device Invitation
    if(isset($_POST['addNewDevice']) && $_POST['addNewDevice'] == session_id()){
        //Check to make sure device has not already been added
        if(!$node_admin->get_id_for_device($_POST['deviceSerial'])){
            // Serial not already being used.
            if($node_admin->create_device_invitation($_POST['deviceSerial'], $_SESSION['id'])){
                $devices_message = "Device invitation created successfully. This invitation is valid for 15 minutes.";
            }
            else{
                $devices_error = "Error creating invitation. ".$GLOBALS['error_message'];
            }
        }
        else{
            $devices_error = "Device is already registered.";
        }
    }

    // Delete Device Invitation
    if(isset($_POST['manageInvitations']) && $_POST['manageInvitations'] == session_id()){
        if(isset($_POST['invitation_id'])){
            foreach($_POST['invitation_id'] as $invitation){
                if($node_admin->delete_device_invitation($invitation[0])){
                    $devices_message = "Device invitation deleted.";
                }
                else{
                    $devices_error = "Error deleting invitation. ".$GLOBALS['error_message'];
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
		<title>Node Dashboard</title>
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
			<name><label class="logo">Node: <? echo ($node_admin->get_node_alias()) ? $node_admin->get_node_alias() : $node_admin->get_node_name(); ?></label></name>
			<?php include 'nav_menu.php'; ?>
		</nav>

        <section class="container">

<!-- Content Title -->
<?php $title = ($node_admin->get_node_alias() != NULL) ? $node_admin->get_node_alias() : $node_admin->get_node_name(); ?>
<?php echo $gui->page_content_title("Node: ".$title); ?>

<div style="overflow:auto">
  <div class="page-menu">
    <a href="#"><i class="fas fa-chevron-down"></i> Node</a>
    <a href="#" style="padding-left: 25px;">Alias</a>
    <a href="#" style="padding-left: 25px;">Clients</a>
    <a href="#" style="padding-left: 25px;">Sharing</a>
  </div>

	<div class="main">

        <section>
            <div class="content">
                <!-- Section: Alias -->
                <?php echo $gui->section_heading("far fa-address-book", $node_alias_title, $node_alias_tooltip); ?>
                <?php echo $gui->section_message($alias_error, $alias_message); ?>

                <!-- Content area -->
                <div class="config-item">
                <form method="post" id="manage-node-alias" name="manage-node-alias" action="node.php?page=node&id=<?php echo $node_admin->get_node_id(); ?>">
                <?php  echo ($node_admin->get_node_alias()) ? $node_admin->get_node_alias() : "No alias set"; ?>
                <input type="hidden" id="clearAlias" name="clearAlias" value="<?= session_id(); ?>">
                </form>
                </div>

                <!-- Node Alias Form -->
                <div class="config-form" id="node-alias-form" style="display: none;">
                    <form method="post" id="set-node-alias" name="set-node-alias" action="node.php?page=node&id=<?php echo $node_admin->get_node_id(); ?>">
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
            <!-- Section: Authorized Devices -->
            <div class="content">
                <?php echo $gui->section_heading("fas fa-sitemap", $node_devices_title, $node_devices_tooltip); ?>
                <?php echo $gui->section_message($devices_error, $devices_message); ?>

                <!-- Content area -->
                <div class="config-item">
                    <!-- Node Device Form -->
                    <form method="post" id="manage-node-device" name="manage-node-device" action="node.php?page=node&id=<?php echo $node_admin->get_node_id(); ?>">   
                    <table>
                        <thead>
                            <tr>
                                <th>Device Name</th>
                                <th>Type</th>
                                <th>Serial</th>
                                <th>Up-Time</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            foreach($node_admin->get_authorized_devices() as $id => $node_dev){
                                echo "<tr><td><input type=\"checkbox\" name=\"device_id[]\" id=\"device-".$id."\" value=\"".$id."\" onclick=\"toggleCheckbox(this, 'dev".$id."')\">
                                <label for=\"device-".$id."\" class=\"";
                                echo ($node_dev['status'] == True)? "online" : "offline"; 
                                echo" table-list-label\">".$node_dev['device_name']."</label></td>";
                                echo "<td>".$node_dev['platform']."</td>";
                                echo "<td>".$node_dev['device_id']."</td>";
                                echo "<td>";
                                echo ($node_dev['status'] == True)? $node_admin->elapsed_time($node_dev['last_reconnect']): "Off-line";
                                echo "</td></tr>";
                            }
                        ?>
                    </table>
                    
                    <input type="hidden" id="manageDevices" name="manageDevices" value="<?= session_id(); ?>">
                    </form>

                    <!-- Device Invitation Form -->
                    <form method="post" id="manage-node-device-invitations" name="manage-node-device-invitations" action="node.php?page=node&id=<?php echo $node_admin->get_node_id(); ?>">
                    <?php 
                        $invitations_list = $node_admin->get_device_invitations(); 
                        if($invitations_list != False){
                            echo 
                            "<table style=\"margin-top:10px;\">
                                <thead>
                                    <tr>
                                        <th>Pending Invitation</th>
                                        <th>Expires</th>
                                        <th>Access Code</th>
                                    </tr>
                                </thead>
                                <tbody>";
                                
                                foreach($invitations_list as $id => $dev){
                                    $expiry = $dev['expires'] - time();
                                    if($expiry > 0){
                                        $formated_time = intdiv($expiry, 60);
                                    }
                                    echo "<tr><td><input type=\"checkbox\" name=\"invitation_id[]\" id=\"invitation-".$id."\" value=\"".$id."\" onclick=\"toggleCheckbox(this, 'invite".$id."')\">
                                    <label for=\"invitation-".$id."\" class=\"table-list-label\">D/N: ".$dev['device_id']."</label></td>";
                                    echo "<td>";
                                    if($expiry > 0){
                                        if($expiry > 1){
                                            echo $formated_time." minutes";
                                        }
                                        else{
                                            echo $formated_time." minute";
                                        }
                                    }
                                    else{
                                        echo "Expired";
                                    }
                                    echo "</td><td>";
                                    if($expiry > 0){
                                        if($dev['is_valid'] != 0){
                                            echo $dev['access_code'];
                                        }
                                        else{
                                            echo "Used";
                                        }
                                    }
                                    else{
                                        echo "Revoked";
                                    }
                                    echo "</td></tr>";
                                }
                                echo
                                "</tbody>
                            </table>";
                        }
                    ?>
                    <input type="hidden" id="manageInvitations" name="manageInvitations" value="<?= session_id(); ?>">
                    </form>
                </div>

                <!-- Form: Add Device -->
                <div class="config-form" id="add-device-form" style="display: none;">
                <div style="color: black; padding-bottom: 5px;"><b>Instructions</b>: Input the serial number and IP address for your board (Settings screen)</div>
                <form action="node.php?page=node&id=<?php echo $node_admin->get_node_id(); ?>" method="post" id="add_device" name="add_device">
                    <input type="text" name="deviceSerial" id="deviceSerial" placeholder="Device Serial #">
                    <input type="text" name="deviceIP" id="deviceIP" placeholder="IP Address" value="<?php echo getIPAddress(); ?>">
                    <input type="hidden" id="addNewDevice" name="addNewDevice" value="<?= session_id(); ?>">                 
                    <button class="form-button" id="add-device-button" type="button" onclick="document.getElementById('add_device').submit(); return false;"> Add Device </button>
                </form>
                </div>

                <!-- Action Buttons -->
                <div class="actions">
                <a href="javascript:{}" onclick='showForm("add-device-form")'><i class="fas fa-laptop-medical" aria-hidden="true"></i><label>Add Device</label></a>
                <a href="javascript:{}" onclick="document.getElementById('manage-node-device').submit(); return false;"><i class="fas fa-trash-alt" aria-hidden="true"></i><label>Remove Device</label></a>
                <a href="javascript:{}" onclick="document.getElementById('manage-node-device-invitations').submit(); return false;"><i class="fas fa-file-excel" aria-hidden="true"></i><label>Cancel Invitation</label></a>
                </div>

            </div>
        </section>


        <section>
            <!-- Section: Clients -->
            <div class="content">
                <?php echo $gui->section_heading("fas fa-user-friends", $node_clients_title, $node_clients_tooltip); ?>
                <?php echo $gui->section_message($owner_error, $owner_message); ?>

                <!-- Content area -->
                <div class="config-item">
                    <!-- Node Owner Form -->
                    <form method="post" id="set-node-owner" name="set-node-owner" action="node.php?page=node&id=<?php echo $node_admin->get_node_id(); ?>">   
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
                <form action="node.php?page=node&id=<?php echo $node_admin->get_node_id(); ?>" method="post" id="change_access" name="change_access">
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

                    <?php
                    if($_SESSION['user_level'] >= 3){
                        echo
                        "<input class=\"radio-button\" type=\"radio\" id=\"server\" name=\"user_level\" value=\"3\">
                        <label class=\"radio-button-label\" for=\"server\">Server Admin</label>";
                    }
                    ?>
                    
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

                <!-- Content area -->
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