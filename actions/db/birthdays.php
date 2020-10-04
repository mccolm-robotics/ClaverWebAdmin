<?php
// Prepared statements: https://websitebeaver.com/prepared-statements-in-php-mysqli-to-prevent-sql-injection

// ==== Accounts ====
// [1]  - (S) get_all_accounts()     R=> MySQLi result obj
// [2]  - (U) set_birthday($birthday, $user_id)     R=> bool
// [3]  - (S) get_birthday($user_id)    R=> date
// [4]  - (I) create_user_birthday($user_id, $name, $month, $day, $birthday)    R=> bool
// [5]  - (D) delete_user_birthday($user_id)    R=> del


  ///////////////////////////////////////////
 ////  TABLE: Birthdays  ///////////////////
///////////////////////////////////////////

class Birthdays{
    private $connection;

    function __construct($connection){
        $this->connection = $connection;
    }

    // [1]
    // SELECT: Gets all data from the accounts table
    // RETURN: array of vals
    public function get_all_accounts() {
        $sql = "SELECT user_id, birthday FROM birthdays";
        $result = $this->connection->query($sql);
        
        if ($result->num_rows > 0) {
        return $result;
        }
        else{
            return NULL;
        }
    }

    // [2]
    // UPDATE: Set the 'birthday' for user with 'id' value of account
    //-- $birthday: The 'birthday' value for an account (date(STR))
    //-- $user_id: the 'id' value of an existing account (INT)
    // RETURN: (bool)
    public function set_birthday($month, $day, $birthday, $user_id){
        if ($stmt = $this->connection->prepare('UPDATE birthdays SET month=?, day=?, birthday=? WHERE user_id = ?')) {
            $stmt->bind_param('iisi', $month, $day, $birthday, $user_id);
            $status = $stmt->execute();
            $stmt->close();
            if($status === false){
                $GLOBALS['error_message'] = "SQL UPDATE FAILED: [Birthdays] Set Birthday";
                return false;
            }            
            return true;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Birthdays] Set Birthday";
            return false;
        }
    }

    // [3]
    // SELECT: Gets the birthday for a user by 'user_id'
    //-- $user_id: The 'id' value from an existing accounts row (INT)
    // RETURN: 'user_level' value of user (INT)
    public function get_birthday($user_id){
        if ($stmt = $this->connection->prepare('SELECT birthday FROM birthdays WHERE user_id = ?')) {
            // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            // Store the result so we can check if the account exists in the database.
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($birthday);
                $stmt->fetch();                
            } else {
                return false;
            }
            $stmt->close();
            return $birthday;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Birthdays] Get Birthday";
            return false;
        }
    }

    // [4]
    // INSERT: Create a birthday entry for a registered user
    //-- $user_id: 'id' value from accounts table (INT)
    //-- $name: First name of user listed in accounts table (STR)
    //-- $month: Month of birth (INT)
    //-- $day: Day of birth (INT)
    //-- $birthday: YYYY-MM-DD formated date for birthday
    // RETURN: bool
    public function create_user_birthday($user_id, $name, $month, $day, $birthday){
        if ($stmt = $this->connection->prepare('INSERT INTO birthdays(user_id, name, month, day, birthday) VALUES (?, ?, ?, ?, ?)')) {
            // Bind parameters (s = string, i = int, b = blob, d = double), in our case the username is a string so we use "s"
            $stmt->bind_param('isiis', $user_id, $name, $month, $day, $birthday);
            $status = $stmt->execute();
            $stmt->close();
            if($status === false){
                $GLOBALS['error_message'] = "SQL Insert FAILED: [Birthdays] Create Birthday";
                return false;
            }            
            return true;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Birthdays] Create Birthday";
            return false;
        }
    }

    // [5]
    // DELETE: Deletes the birthday record for a user with a corresponding id in the accounts table
    //-- $user_id: 'id' value of user from accounts table (INT)
    // RETURN: boolean
    public function delete_user_birthday($user_id){
        if ($stmt = $this->connection->prepare('DELETE FROM birthdays WHERE user_id = ?')) {
            $stmt->bind_param('i', $user_id);
            $status = $stmt->execute();
            $stmt->close();
            if($status === false){
                $GLOBALS['error_message'] = "SQL Delete FAILED: [Birthdays] Delete Birthday";
                return false;
            }            
            return true;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Birthdays] Delete Birthday";
            return false;
        }
    }
}

?>