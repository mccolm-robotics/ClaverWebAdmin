<?php
// Prepared statements: https://websitebeaver.com/prepared-statements-in-php-mysqli-to-prevent-sql-injection

// ==== Accounts ====
// [1]  - (S) get_all_accounts()     R=> MySQLi result obj
// [2]  - (D) delete_user_account($user_id)
// [3]  - (S) get_account_id($username)
// [4]  - (S) check_account_id($id_array)
// [5]  - (U) set_password($user_id, $password)
// [6]  - (S) get_user_data($user_id)    R=> array of values
// [7]  - (S) get_full_name($user_id)    R=> array
// [8]  - (U) set_user_level($user_level, $user_id)  R=> bool
// [9]  - (S) get_user_level($user_id)   R=> int
// [10] - (U) set_email($email, $user_id)    R=> bool


  ///////////////////////////////////////////
 ////  TABLE: ACCOUNTS  ////////////////////
///////////////////////////////////////////

class Accounts{
    private $connection;

    function __construct($connection){
        $this->connection = $connection;
    }

    // [1]
    // SELECT: Gets all data from the accounts table
    // RETURN: array of vals
    public function get_all_accounts() {
        $sql = "SELECT id, username, user_level FROM accounts";
        $result = $this->connection->query($sql);
        
        if ($result->num_rows > 0) {
        return $result;
        }
        else{
            return NULL;
        }
    }

    // [2]
    // DELETE: Removes a user from the accounts table
    //-- $user_id: 'id' value of user from accounts table (INT)
    // RETURN: boolean
    public function delete_user_account($user_id){
        if ($stmt = $this->connection->prepare('DELETE FROM accounts WHERE id = ?')) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->close();
            return true;
        }
        else{
            $GLOBALS['error_message'] = "Bad SQL Query: [Accounts] Delete User Account";
            return false;
        }
    }

    // [3]
    // SELECT: Gets the account id of a user
    //-- $username: The login username of a user in accounts table (STR)
    // RETURN: 'id' value of user from accounts table. Used by create_new_account() - (INT)
    public function get_account_id($username){
        if ($stmt = $this->connection->prepare('SELECT id FROM accounts WHERE username = ?')) {
            // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
            $stmt->bind_param('s', $username);
            $stmt->execute();
            // Store the result so we can check if the account exists in the database.
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id);
                $stmt->fetch();                
            } else {
                return false;
            }
            $stmt->close();
            return $id;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Accounts] Get Account ID";
            return false;
        }
    }

    // [4]
    // SELECT: Checks to see if accout(s) exist
    //-- $id_array: list of account 'id' values
    // RETURN: boolean
    public function check_account_id($id_array){

        $overall_success = true;
        $success_array = array();

        if ($stmt = $this->connection->prepare('SELECT id FROM accounts WHERE id = ?')) {
            $stmt->bind_param('i', $id);

            if(is_array($id_array)){
                foreach($id_array as $id){
                    $stmt->execute();
                    // Store the result so we can check if the account exists in the database.
                    $stmt->store_result();
                    
                    if ($stmt->num_rows > 0){
                        if($overall_success != false){
                            $overall_success = true;
                        }
                        array_push($success_array, true);
                    }
                    else{
                        $overall_success = false;
                        array_push($success_array, false);
                    }
                }
                $stmt->close();
                return array($overall_success, $success_array);
            }
            else{
                $id = $id_array;
                $stmt->execute();
                // Store the result so we can check if the account exists in the database.
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $stmt->close();
                    return true;
                } else {
                    return false;
                }
            }
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Accounts] Check Account ID";
            return false;
        }
    }

    // [5]
    // UPDATE: sets user password
    //-- $user_id: 'id' value from accounts table
    //-- $password: 'password' value from accounts table
    // RETURN: boolean
    public function set_password($user_id, $password){
        if ($stmt = $this->connection->prepare('UPDATE accounts SET password=? WHERE id=?')) {
            // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
            $stmt->bind_param('si', $password, $user_id);
            $status = $stmt->execute();
            // Store the result so we can check if the account exists in the database.
            $stmt->close();
            if($status === false){
                $GLOBALS['error_message'] = "SQL UPDATE FAILED: [Accounts] Set Password";
                return false;
            }            
            return true;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Accounts] Set Password";
            return false;
        }
    }

    // [6]
    // SELECT: Gets all data for user using $user_id
    //-- $user_id: int value corresponding to the 'id' value for user in Accounts table
    // RETURN: array of vals
    public function get_user_data($user_id) {
        if ($stmt = $this->connection->prepare('SELECT * FROM accounts WHERE id = ?')) {
            // Bind parameters (s = string, i = int, b = blob, etc)
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows === 0) exit('No rows');
            $data = $result->fetch_assoc();
            $stmt->close();
            return $data;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Accounts] Get Account Data";
            return false;
        }
    }

    // [7]
    // SELECT: Gets the first and last name of user
    //-- $user_id: int value corresponding to the 'id' value for account in accounts table
    // RETURN: array of vals
    public function get_full_name($user_id) {
        if ($stmt = $this->connection->prepare('SELECT first_name, last_name FROM accounts WHERE id = ?')) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result(); // get the mysqli result
            $full_name = $result->fetch_all(MYSQLI_ASSOC); // fetch data  
            $stmt->close();
            return $full_name;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Accounts] Get Full Name";
            return false;
        }
    }

    // [8]
    // UPDATE: set the user level for account using 'id' value of account
    //-- $user_level: the 'user_level' value for an account (INT)
    //-- $user_id: the 'id' value of an existing account (INT)
    // RETURN: (bool)
    public function set_user_level($user_level, $user_id){
        if ($stmt = $this->connection->prepare('UPDATE accounts SET user_level = ? WHERE id = ?')) {
            // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
            $stmt->bind_param('ii', $user_level, $user_id);
            $status = $stmt->execute();
            // Store the result so we can check if the account exists in the database.
            $stmt->close();
            if($status === false){
                $GLOBALS['error_message'] = "SQL UPDATE FAILED: [Accounts] Set User Level";
                return false;
            }            
            return true;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Accounts] Set User Level";
            return false;
        }
    }

    // [9]
    // SELECT: Gets the user-level of a user
    //-- $user_id: The 'id' value from an existing accounts row (INT)
    // RETURN: 'user_level' value of user (INT)
    public function get_user_level($user_id){
        if ($stmt = $this->connection->prepare('SELECT user_level FROM accounts WHERE id = ?')) {
            // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
            $stmt->bind_param('s', $user_id);
            $stmt->execute();
            // Store the result so we can check if the account exists in the database.
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($user_level);
                $stmt->fetch();                
            } else {
                return false;
            }
            $stmt->close();
            return $user_level;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Accounts] Get User Level";
            return false;
        }
    }

    // [10]
    // UPDATE: set the email for account using 'id' value of account
    //-- $email: the 'email' value for an account (STR)
    //-- $user_id: the 'id' value of an existing account (INT)
    // RETURN: (bool)
    public function set_email($email, $user_id){
        if ($stmt = $this->connection->prepare('UPDATE accounts SET email = ? WHERE id = ?')) {
            $stmt->bind_param('si', $email, $user_id);
            $status = $stmt->execute();
            $stmt->close();
            if($status === false){
                $GLOBALS['error_message'] = "SQL UPDATE FAILED: [Accounts] Set Email";
                return false;
            }            
            return true;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Accounts] Set Email";
            return false;
        }
    }
}

?>