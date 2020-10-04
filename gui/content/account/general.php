<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/actions/account_mgmt.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/actions/utility/gui_builder.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/gui/gui_text.php');

$gui = new Gui_Builder();
$account_admin = new Account_Management($connection);

$profile_error = NULL;
$profile_message = NULL;

///// Manage Form Actions /////

    // Reset password
    if(isset($_POST['changePassword']) && $_POST['changePassword'] == session_id()){
        if($_POST['new_password'] == "" || $_POST['confirm_password'] == ""){
            $profile_error = "ERROR: Please enter a password in both fields";
        }
        elseif($_POST['new_password'] != $_POST['confirm_password']){
            $profile_error = "ERROR: Both passwords must match";
        }
        elseif(!$account_admin->check_password_strength($_POST['new_password'])){
            $profile_error = $error_message;
        }
        else{
            if($account_admin->set_password(password_hash($_POST['new_password'], PASSWORD_DEFAULT))){
                $profile_message = "Password changed";
            }
            else{
                if($error_message == NULL){
                    $profile_error = "ERROR: Unable to change password";
                }
                else{
                    $profile_error = $error_message;
                }
            }
        }
    }

    // Change birthday
    if(isset($_POST['changeBirthday']) && $_POST['changeBirthday'] == session_id()){
        if(isset($_POST['birthday']) && $_POST['birthday'] != NULL){
            if($account_admin->validate_date_format($_POST['birthday']) && $account_admin->validate_date($_POST['birthday'])){
                if($account_admin->set_birthday($_POST['birthday'])){
                    $profile_message = "Birthday updated";
                }
                else{
                    $profile_error = "Error: Unable to update birthday";
                }
            }
            else{
                if($error_message == NULL){
                    $profile_error = "Please enter a valid date";
                }
                else{
                    $profile_error = $error_message;
                }
            }
        }
    }

    // Change email
    if(isset($_POST['changeEmail']) && $_POST['changeEmail'] == session_id()){
        if(isset($_POST['email']) && $_POST['email'] != NULL){
            if($account_admin->validate_email($_POST['email'])){
                if($account_admin->set_email($_POST['email'])){
                    $profile_message = "Email address updated";
                }
                else{
                    if($error_message == NULL){
                        $profile_error = "ERROR: Unable to change email address";
                    }
                    else{
                        $profile_error = $error_message;
                    }
                }
            }
            else{
                $profile_error = "Please enter a valid email address";
            }

        }
    }

// Test 
// $account_admin->printArray($_POST);



?>

<html lang="en" dir="ltr">
	<head>
		<meta charset="utf-8">
		<title>Account Dashboard</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <link rel="stylesheet" type="text/css" href="/assets/css/dashboard.css">  
        <link rel="stylesheet" type="text/css" href="/assets/css/account.css">   
		<link rel="stylesheet" href="/assets/fonts/font-awesome-5.14.0/css/all.css">
        <script src="/assets/js/management.js"></script>
	</head>

	<body>
		<nav>
			<input type="checkbox" id="check">
			<label for="check" class="checkbtn">
				<i class="fas fa-bars"></i>
            </label>
            <!-- Navigation Title -->
			<name><label class="logo">Account: <? echo $account_admin->get_first_name();?> <? echo $account_admin->get_last_name();?></label></name>
			<?php include 'nav_menu.php'; ?>
		</nav>

        <section class="container">

            <!-- Content Title -->
            <?php echo $gui->page_content_title("Account: ".$account_admin->get_first_name()." ".$account_admin->get_last_name()); ?>

            <div style="overflow:auto">

                <!-- Directory Side Menu -->
                <div class="page-menu">
                    <a href="#"><i class="fas fa-chevron-down"></i> Account</a>
                    <a href="#" style="padding-left: 25px;">Profile</a>
                </div>

                <div class="main">

                    <!-- Account Profile -->
                    <section>
                        <div class="content">
                            <?php echo $gui->section_heading("far fa-address-card", $account_profile_title, $account_profile_tooltip); ?>
                            <?php echo $gui->section_message($profile_error, $profile_message); ?>

                            <div class="config-item">
                                <div class="profile-data-container">
                                    <div class="profile-column profile-avatar"><div class="profile-avatar-constraint"><img src="/assets/images/avatar.png" alt="Avatar" class="avatar"></div></div>
                                    <div class="profile-column profile-details">
                                        <table id="profile-data-table">
                                            <tr class="mangaes">
                                                <td style="vertical-align: middle;">Username: </td><td><?php echo $account_admin->get_username(); ?></td><td></td>
                                            <tr>
                                                <td style="vertical-align: middle;">Birthday: </td><td><div id="dob"><?php echo date("F j, Y", strtotime($account_admin->get_birthday())); ?></div></td><td style="vertical-align: middle;"><div id="birthday-change-button"><a onclick="replace_div('dob', 'change-birthday-form', 'birthday-change-button', 'birthday-save-button')"><i class="fas fa-cog"></i></a></div></td>
                                            </tr>
                                            <tr>
                                                <td style="vertical-align: middle;">Email: </td><td><div id="email"><?php echo $account_admin->get_email(); ?></div></td><td style="vertical-align: middle;"><div id="email-change-button"><a onclick="replace_div('email', 'change-email-form', 'email-change-button', 'email-save-button')"><i class="fas fa-cog"></i></a></div></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Form: Change Birthday -->
                            <div class="config-form" id="change-birthday-form" style="display: none;">
                                <form action="account.php" method="post" id="change_birthday" name="change_birthday">
                                    <input type="date" name="birthday" id="birthday" style="padding: 2px; color:#666; width:200px;">
                                    <input type="hidden" id="changeBirthday" name="changeBirthday" value="<?= session_id(); ?>">
                                </form>
                            </div>

                            <!-- Form: Change Birthday - Save Button -->
                            <div id="birthday-save-button" style="display: none;">
                                <a onclick="document.getElementById('change_birthday').submit(); return false;"><i class="fas fa-save"></i></a>
                            </div>

                            <!-- Form: Change Email -->
                            <div class="config-form" id="change-email-form" style="display: none;">
                                <form action="account.php" method="post" id="change_email" name="change_email">
                                    <input class="text-field" type="email" name="email" id="email" placeholder="<? echo $account_admin->get_email();?>">
                                    <input type="hidden" id="changeEmail" name="changeEmail" value="<?= session_id(); ?>">
                                </form>
                            </div>

                            <!-- Form: Change Email - Save Button -->
                            <div id="email-save-button" style="display: none;">
                                <a onclick="document.getElementById('change_email').submit(); return false;"><i class="fas fa-save"></i></a>
                            </div>

                            <!-- Form: Change Password -->
                            <div class="config-form" id="change-password-form" style="display: none;">
                                <form action="account.php" method="post" id="change_password" name="change_password">
                                    <input type="password" name="new_password" id="new_password" placeholder="New Password">
                                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password">
                                    <input type="hidden" id="changePassword" name="changePassword" value="<?= session_id(); ?>">
                                    <button class="form-button" id="form-button" type="button" onclick="document.getElementById('change_password').submit(); return false;"> OK </button>
                                </form>
                            </div>

                            <!-- Action Buttons -->
                            <div class="actions">
                                <a href="javascript:{}" onclick='showForm("change-password-form")'><i class="fas fa-key" aria-hidden="true"></i><label>Change Password</label></a>                       
                                
                            </div>

                        </div>
                    </section>  <!-- End Profile -->

                </div>
            </div>

        </section>
    </body>
</html>           