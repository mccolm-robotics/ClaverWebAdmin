<?php
session_start();

include 'connect.php';

if ( !isset($_POST['username'], $_POST['password']) ) {
	// Could not get the data that should have been sent.
	exit('Please fill both the username and password fields!');
}

// Prepare our SQL, preparing the SQL statement will prevent SQL injection.
if ($stmt = $connection->prepare('SELECT id, password, user_level FROM accounts WHERE username = ?')) {
	// Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
	$stmt->bind_param('s', $_POST['username']);
	$stmt->execute();
	// Store the result so we can check if the account exists in the database.
	$stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password, $user_level);
        $stmt->fetch();
        // Account exists, now we verify the password.
        // Note: remember to use password_hash in your registration file to store the hashed passwords.
        if (password_verify($_POST['password'], $password)) {
            // Verification success! User has loggedin!
            // Create sessions so we know the user is logged in, they basically act like cookies but remember the data on the server.
            session_regenerate_id();
            $_SESSION['loggedin'] = TRUE;
            $_SESSION['name'] = $_POST['username'];
            $_SESSION['id'] = $id;
            $_SESSION['user_level'] = $user_level;

            $stmt = $connection->prepare('SELECT node_id FROM node_members WHERE user_id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($node_id);
                    $stmt->fetch();

                    $stmt = $connection->prepare('SELECT node_name FROM nodes WHERE id = ?');
                        // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
                        $stmt->bind_param('i', $node_id);
                        $stmt->execute();
                        // Store the result so we can check if the account exists in the database.
                        $stmt->store_result();
                        if ($stmt->num_rows > 0) {
                            $stmt->bind_result($node_name);
                            $stmt->fetch();  
                            $_SESSION['node_name'] = $node_name;              
                        }
                }
                else{
                    $_SESSION['node_name'] = "Guest";
                }  

            if($user_level == 3){
                header('Location: ../gui/dashboard.php');
            }
            else{
                header('Location: ../gui/node.php');
            }
        } else {
            $_SESSION['login-error'] = 'Incorrect password';
            header('Location: ../index.php');
            exit;
        }
    } else {
        $_SESSION['login-error'] = 'Incorrect username';
        header('Location: ../index.php');
        exit;
    }
	$stmt->close();
}

?>