<?php
if($_SESSION['user_level'] < 3){
    header('Location: node.php');
}


require_once($_SERVER['DOCUMENT_ROOT'] . '/actions/user_mgmt.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/actions/utility/gui_builder.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/actions/utility/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/gui/gui_text.php');

$admin = new User_Management($connection);
$gui = new Gui_Builder();

$account_error = NULL;
$account_message = NULL;
$node_error = NULL;
$node_message = NULL;


///// Manage Form Actions /////

    // Create a new user
    if(isset($_POST['addUser']) && $_POST['addUser'] == session_id()){
        if($_POST['username'] == "" || $_POST['password'] == "" || $_POST['email'] == "" || $_POST['birthday'] == "" || $_POST['user_level'] == "" || $_POST['first_name'] == "" || $_POST['last_name'] == "" || $_POST['node'] == ""){
            $account_error = "Error: Please fill in all fields and try again";
        }
        elseif(!$admin->check_password_strength($_POST['password'])){
            $account_error = $error_message;
        }
        else{
            $child_node = (isset($_POST['child_node'])) ? true : false;
            $new_owner = (isset($_POST['new_owner'])) ? true : false;
            if($admin->create_new_account($_POST['username'], password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['email'], $_POST['birthday'], $_POST['user_level'], $_POST['first_name'], $_POST['last_name'], $_POST['node'], $child_node, $new_owner)){
                $account_message = "New user created.";
            }
            else{
                if($error_message == NULL){
                    $account_error = "ERROR: Unable to create new user.";
                }
                else{
                    $account_error = $error_message;
                }
            }
        }
        
    }

    // Delete user(s)
    if(isset($_POST['removeUser']) && $_POST['removeUser'] == session_id()){
        if(isset($_POST['account_id'])){
            $check_if_exist = $admin->accounts->check_account_id($_POST['account_id']);
            // Add some logic to make sure the user is committed to deleting accounts
            if($check_if_exist[0] == true){
                foreach($_POST['account_id'] as $account_id){
                    if($admin->delete_account($account_id)){
                        $account_message = "Account removed";
                    }
                    else{
                        if($error_message == NULL){
                            $account_error = "ERROR: Problem removing account.";
                        }
                        else{
                            $account_error = $error_message;
                        }
                    }
                }
            }
        }
    }

    // Reset password
    if(isset($_POST['changePassword']) && isset($_POST['password_account']) && $_POST['changePassword'] == session_id()){
        if($_POST['new_password'] == "" || $_POST['confirm_password'] == ""){
            $account_error = "ERROR: Please enter a password in both fields";
        }
        elseif($_POST['new_password'] != $_POST['confirm_password']){
            $account_error = "ERROR: Both passwords must match";
        }
        elseif(count($_POST['password_account']) > 1){
            $account_error = "ERROR: Unable to change passwords for multiple accounts";
        }
        elseif(!$admin->check_password_strength($_POST['new_password'])){
            $account_error = $error_message;
        }
        else{
            if($admin->accounts->set_password($_POST['password_account'][0], password_hash($_POST['new_password'], PASSWORD_DEFAULT))){
                $account_message = "Password changed";
            }
            else{
                if($error_message == NULL){
                    $account_error = "ERROR: Unable to change password";
                }
                else{
                    $account_error = $error_message;
                }
            }
        }
    }

    // Delete node
    if(isset($_POST['removeNode']) && $_POST['removeNode'] == session_id()){
		$admin->initialize();
        $membership = $admin->get_all_node_members();
        $client_tally = $admin->get_client_tally();
        if(isset($_POST['node_id'])){
			$nodes_are_empty = true;
			if(in_array($_POST['node_id'], $client_tally)){
				foreach($_POST['node_id'] as $node_id){
					if($client_tally[$node_id] > 0){
						$nodes_are_empty = false;
					}
				}
				
			}
            if(!$nodes_are_empty){
				$node_error = "ERROR: Nodes must have zero clients before being deleted";
			}
            else{
                foreach($_POST['node_id'] as $node_id){
                    if($admin->nodes->delete_node($node_id)){
                        $node_message = "Node removed";
                    }
                    else{
                        if($error_message == NULL){
                            $node_error = "ERROR: Unable to remove node";
                        }
                        else{
                            $node_error = $error_message;
                        }
                    }
                }
            }
        }
    }

$admin->initialize();
// Organize data used to build the dashboard
$accounts = $admin->get_all_accounts();
$nodes = $admin->get_all_nodes();
$nodes_id = $admin->get_all_node_ids();
$membership = $admin->get_all_node_members();
$client_tally = $admin->get_client_tally();

?>


<html lang="en" dir="ltr">
	<head>
		<meta charset="utf-8">
		<title>Admin Dashboard</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" type="text/css" href="/assets/css/dashboard.css">
		<link rel="stylesheet" href="/assets/fonts/font-awesome-5.14.0/css/all.css">
		<script src="/assets/js/management.js"></script>
		<style type="text/css">
            .buttons {
                font-size: 4em;
                display: flex;
                justify-content: center;
            }
            .button, .value {
                line-height: 1;
                padding: 2rem;
                margin: 2rem;
                border: medium solid;
                min-height: 1em;
                min-width: 1em;
            }
            .button {
                cursor: pointer;
                user-select: none;
            }
            .minus {
                color: red;
            }
            .plus {
                color: green;
            }
            .value {
                min-width: 2em;
            }
            .state {
                font-size: 2em;
            }
        </style>
	</head>

	<body>

		<nav>
			<input type="checkbox" id="check">
			<label for="check" class="checkbtn">
				<i class="fas fa-bars"></i>
			</label>
			<name><label class="logo">Admin Dashboard</label></name>
			<?php include 'nav_menu.php'; ?>
		</nav>



<section class="container">

<?php echo $gui->page_content_title("<i class=\"fas fa-home\" style=\"font-size: 18pt; width: 30px; margin-right:10px;\"></i>General"); ?>

<div style="overflow:auto">
  <div class="page-menu">
    <a href="#"><i class="fas fa-chevron-right"></i> Nodes</a>
    <a href="#"><i class="fas fa-chevron-right"></i> Connected Boards</a>
    <a href="#"><i class="fas fa-chevron-right"></i> Accounts</a>
  </div>

	<div class="main">
    <section>
		
		<div class="content">
			<!-- Section: Nodes -->
			<?php echo $gui->section_heading("fas fa-sitemap", $dash_title_nodes, $dash_nodes_tooltip); ?>
			<?php echo $gui->section_message($node_error, $node_message); ?>

			<div class="config-item">
			<form action="dashboard.php" method="post" id="manage_node">
			<table>
					<thead>
						<tr>
							<th>Node ID</th>
							<th>Level</th>
							<th>Owner</th>
							<th>Parent Node</th>
							<th>Clients</th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach($nodes as $row){
							//
							if($row['node_owner'] >= 0){	// exclude the server as a node
								echo "
								<tr>
									<td><input type=\"checkbox\" name=\"node_id[]\" value=\"".$row['id']."\"><a href=\"dashboard.php?page=node&id=".$row['id']."\">".$row['node_name']."</a></td>
									<td>".$row['depth']."</td>
									<td>";
									echo (array_key_exists($row['node_owner'], $accounts))? $accounts[$row['node_owner']]['username']:"<div id=\"error\">NONE</div>";
								echo "</td>
									<td>".$nodes_id[$row['child_of']]['node_name']."</td>
									<td>";
									echo (array_key_exists($row['id'], $client_tally))? $client_tally[$row['id']]:"0";
								echo "</td>
								</tr>";
							}
						}
						?>
					</tbody>
				</table>
				<input type="hidden" id="removeNode" name="removeNode" value="<?= session_id(); ?>">
				</form>
			</div>
			<div class="config-form" id="node-form" style="display: none;">
				<input type="text" name="username" id="username" placeholder="Node Name">
				<button id="form-button" type="submit"> Add </button>
			</div>
			<div class="actions">
			<a class="node-add" onclick='showForm("node-form")'><i class="fas fa-plus-circle" aria-hidden="true"></i><label>Add</label></a>
			<a href="javascript:{}" onclick="document.getElementById('manage_node').submit(); return false;"><i class="fas fa-minus-circle" aria-hidden="true"></i><label>Remove</label></a>
			<a href="#"><i class="fas fa-people-arrows" aria-hidden="true"></i><label>Transfer Users</label></a>
			</div>
			
			<!-- Section: Connected Message Boards -->
			<?php echo $gui->section_heading("fas fa-plug", $dash_title_connected, $dash_connected_tooltip); ?>

			<?php 
				$uuid = bin2hex(random_bytes(16)); 
				$ip_addr = getIPAddress();
			?>
			
			<div class="config-item">
				<div class="buttons">
					<div class="minus button">-</div>
					<div class="value">?</div>
					<div class="plus button">+</div>
				</div>
				<div class="state">
					<span class="users">?</span> online
				</div>
				<script>
					var minus = document.querySelector('.minus'),
						plus = document.querySelector('.plus'),
						value = document.querySelector('.value'),
						users = document.querySelector('.users'),
						websocket = new WebSocket("ws://127.0.0.1:6789");

					function minus_val(){
						websocket.send(JSON.stringify({mode: 'WhiteBoard', action: 'minus'}));
					}

					minus.onclick = function(event){
						minus_val();
					}

					plus.onclick = function (event) {
						websocket.send(JSON.stringify({mode: 'WhiteBoard', action: 'plus'}));
					}

					websocket.onopen = function (e){
						websocket.send(JSON.stringify({agent: 'browser', bid: 'cb6070bf-e9aa-11ea-97ab-7085c2d6de41', token: '958058', mode: 'WhiteBoard'}));
					}

					websocket.onmessage = function (event) {
						data = JSON.parse(event.data);
						switch (data.type) {
							case 'state':
								value.textContent = data.value;
								break;
							case 'users':
								users.textContent = (data.count.toString() + " user" + (data.count == 1 ? "" : "s"));
								break;
							//default:
								//console.error("unsupported event", data);
						}
					};
				</script>
			</div>
			<div class="actions">
			<a onclick='minus_val()'><i class="fas fa-upload" aria-hidden="true"></i><label>Upgrade</label></a>
			<a href="#"><i class="fa fa-sync" aria-hidden="true"></i><label>Reboot</label></a>
			<a href="#"><i class="fas fa-minus-circle" aria-hidden="true"></i><label>Remove</label></a>
			</div>
			
			<!-- Section: Accounts -->
			<?php echo $gui->section_heading("fas fa-users", $dash_title_accounts, $dash_accounts_tooltip); ?>
			<?php echo $gui->section_message($account_error, $account_message); ?>

			<div class="config-item">
				<form action="dashboard.php" method="post" id="manage_user">
				<table>
					<thead>
						<tr>
							<th>Account</th>
							<th>Role</th>
							<th>Node ID</th>
						</tr>
					</thead>
					<tbody>
						<?php
						
						foreach($accounts as $row){

							echo "
							<tr>
								<td><input type=\"checkbox\" name=\"account_id[]\" value=\"".$row['id']."\" onclick=\"toggleCheckbox(this, 'password".$row['id']."')\"><a href=\"dashboard.php?page=account&id=".$row['id']."\">".$row['username']."</a></td>
								<td>".$admin->account_access_label($row['user_level'])."</td>
								<td>".$nodes_id[$membership[$row['id']]['node_id']]['node_name']."</td>
							</tr>";
						}
						?>
					</tbody>
				</table>
				<input type="hidden" id="removeUser" name="removeUser" value="<?= session_id(); ?>">
				</form>		
			</div>
			<!-- Form: Add New User -->
			<div class="config-form" id="account-form" style="display: none;">
				<form action="dashboard.php" method="post" id="new_user" name="new_user">
					<div class="settings-header">
						<h4>Add New User</h4>
					</div>
					<div class="settings-container">
						<!-- Lvl 1 - Left -->
						<div class="settings left">
				
							<input class="text-field" type="text" name="username" id="username" placeholder="Username">
							<input class="text-field" type="password" name="password" id="password" placeholder="Password">
							<input class="text-field" type="text" name="first_name" id="first_name" placeholder="First Name">
							<input class="text-field" type="text" name="last_name" id="last_name" placeholder="Last Name">
							<input class="text-field" type="date" name="birthday" id="birthday" style="padding:8px 0px; color:#666; width:270px;">
							<input class="text-field" type="email" name="email" id="email" placeholder="Email Address">

						</div>
						<!-- Lvl 1 - Right -->
						<div class="settings right">
							<!-- Nested Columns -->
							<div class="settings-nested-container">
								<!-- Lvl 2 - Left -->
								<div class="settings-nested left">
							
									<h4>Access Level:</h4>
									<input class="radio-button" type="radio" id="user" name="user_level" value="1">
									<label class="radio-button-label" for="user">User</label><BR>
									<input class="radio-button" type="radio" id="node" name="user_level" value="2" checked>
									<label class="radio-button-label" for="node">Node Admin</label><BR>
									<input class="radio-button" type="radio" id="server" name="user_level" value="3">
									<label class="radio-button-label" for="server">Server Admin</label>
							
								</div>
								<!-- Lvl 2 - Right -->
								<div class="settings-nested right">
								
									<h4>Attach to node:</h4>
									<?php
									foreach($nodes as $row){
										if($row['node_owner'] >= 0){	// exclude the server as a node
											echo "
											<input type=\"radio\" id=\"".$row['node_name']."\" name=\"node\" value=\"".$row['id']."\"><label for=\"".$row['node_name']."\">".$row['node_name']."</label><BR>";
										}
									}
									?>
									<input type="radio" id="new" name="node" value="1" checked><label for="new">Create a new top-level node</label><BR>
									<div id="options-box">
										<input type="checkbox" id="child_node" name="child_node" value="1"><label for="child_node">Create a child of the selected node</label><BR>
										<input type="checkbox" id="new_owner" name="new_owner" value="1"><label for="new_owner">Make user the new node owner</label>
									</div>

								</div>
							</div>
							<!-- Lvl 2 - Footer -->
							<div class="settings-nested-footer">
								<h4>Notes:</h4>
								<div class="bullet">Node names are automatically created using the first name of the user.</div>
								<div class="bullet">Check "Create a new child" to assign node to existing node.</div>
								<div class="bullet">New 'Node Admins' take automatic ownership of orphaned nodes.</div>
							</div>
							<!-- End Nested Columns -->
						</div>
					</div>
					<!-- Lvl 1 - Footer -->
					<div class="settings-footer">
						<!-- Form Button -->
						<input type="hidden" id="addUser" name="addUser" value="<?= session_id(); ?>">
						<button class="form-button" type="button" onclick="document.getElementById('new_user').submit(); return false;"> Add </button>
					</div>
				</form>
			</div>
			<!-- Form: Change Password -->
			<div class="config-form" id="change-password-form" style="display: none;">
			<form action="dashboard.php" method="post" id="change_password" name="change_password">
			<?php
				echo "<div  style=\"display: none;\">";
				foreach($accounts as $row){
					echo "
					<input class=\"checkbox-container\" id=\"password".$row['id']."\" type=\"checkbox\" name=\"password_account[]\" value=\"".$row['id']."\">
					";
				}
				echo "</div>";
			?>
				<input type="password" name="new_password" id="new_password" placeholder="New Password">
				<input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password">
				<input type="hidden" id="changePassword" name="changePassword" value="<?= session_id(); ?>">
				<button class="form-button" id="form-button" type="button" onclick="document.getElementById('change_password').submit(); return false;"> OK </button>
				</form>
			</div>
			<div class="actions">
			<a class="account-add" onclick='showForm("account-form")'><i class="fas fa-user-plus" aria-hidden="true"></i><label>Add</label></a>
			<a href="javascript:{}" onclick="document.getElementById('manage_user').submit(); return false;"><i class="fas fa-user-minus" aria-hidden="true"></i><label>Remove</label></a>
			<a class="node-add" onclick='showForm("change-password-form")'><i class="fas fa-user-cog" aria-hidden="true"></i><label>Change Password</label></a>
			</div>
		</div>
		</section>
	</div>
</div>

</section>

</body>
</html>