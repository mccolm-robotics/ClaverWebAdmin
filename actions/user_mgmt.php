<?php
    // Includes
    require_once 'db/nodes.php';
    require_once 'db/accounts.php';
    require_once 'db/node_members.php';
    require_once 'db/birthdays.php';
    require_once 'db/sessions.php';

    // Global variables for passing result messages
    $success_message = NULL;
    $error_message = NULL;

  ///////////////////////////////////////////
 ////  USER MANAGEMENT  ////////////////////
///////////////////////////////////////////


class User_Management{
    private $connection;
    public $nodes;
    public $accounts;
    public $node_members;
    public $sessions;
    private $page_data = array(); // A list of arrays with data created for this page

    private $all_accounts;
    private $all_nodes;
    private $all_node_ids;
    private $all_memberships;
    private $client_tally;

    function __construct($connection){
        $this->connection = $connection;
        $this->nodes = new Nodes($connection);
        $this->accounts = new Accounts($connection);
        $this->node_members = new Node_Members($connection);
        $this->birthdays = new Birthdays($connection);
        $this->sessions = new Sessions($connection);
    }

    // Values needed for constructing the Dashboard view
    public function initialize(){
        $this->all_accounts = $this->get_db_array([$this->accounts, 'get_all_accounts'], 'id');
        $this->all_nodes = $this->get_db_array([$this->nodes, 'get_all_nodes'], 'node_owner');
        $this->all_node_ids = $this->sort_array_by_id($this->all_nodes);
        $this->all_memberships = $this->get_db_array([$this->node_members, 'get_all_members'], 'user_id');
        $this->client_tally = $this->count_node_members($this->all_memberships);
    }

    public function get_all_accounts(){
        return $this->all_accounts;
    }

    public function get_all_nodes(){
        return $this->all_nodes;
    }

    public function get_all_node_ids(){
        return $this->all_node_ids;
    }

    public function get_all_node_members(){
        return $this->all_memberships;
    }

    public function get_client_tally(){
        return $this->client_tally;
    }

    public function create_websocket_session(){
        $uuid = bin2hex(random_bytes(16)); 
        $ip_addr = getIPAddress();
        if($this->sessions->create_websocket_session($uuid, $ip_addr)){
            return $uuid;
        }
        return "Error";
        
    }

    // INSERT: Creates a new user account
    //-- $username: login username access credentials (STR)
    //-- $password: login access credentials (STR)
    //-- $email: user's email address (STR)
    //-- $first_name: User's first name (STR)
    //-- $last_name: User's last name (STR)
    //-- $node: primary key 'id' of the node table (INT)
    //-- $child_node: boolean indicating creation of a new node that connects to an existing node (BOOL)
    //-- $new_owner: boolean indicating that the node's ownership should be assigned to the new user (BOOL)
    //-- RETURN: boolean
    public function create_new_account($username, $password, $email, $birthday, $user_level, $first_name, $last_name, $node, $child_node, $new_owner){
        if($this->validate_name($username) && $this->validate_name($first_name) && $this->validate_name($last_name) && $this->validate_email($email) && $this->validate_birthday($birthday)){
            $first_name = $this->ucname($first_name);
            if(!$this->accounts->get_account_id($username)){
                // Preparing the SQL statement will prevent SQL injection.
                if ($stmt = $this->connection->prepare('INSERT INTO accounts(username, password, email, user_level, first_name, last_name) VALUES (?, ?, ?, ?, ?, ?)')) {
                    // Bind parameters (s = string, i = int, b = blob, d = double), in our case the username is a string so we use "s"
                    $stmt->bind_param('ssssss', $username, $password, $email, $user_level, $first_name, $last_name);
                    $stmt->execute();
                    $stmt->close();
                    $id = $this->accounts->get_account_id($username);
                    // Make sure the account exists
                    if ($id){
                        $this->birthdays->create_user_birthday($id, $first_name, date("n", strtotime($birthday)), date("j", strtotime($birthday)), $birthday);
                        // Create a new top-level account. The value of 0 is set in the HTML form. It is set to 1 on the server
                        if($node == 1){
                            if($this->nodes->create_new_node($first_name, $node, $id)){
                                $node_id = $this->nodes->get_node_ownership($id);
                                // Make sure the node was created
                                if($node_id){
                                    if($this->node_members->set_node_membership($id, $node_id)){
                                        return true;
                                    }
                                }
                            }
                        }
                        else{
                            // Create a new child node                          
                            if($child_node){
                                $parent_depth = $this->nodes->get_node_depth($node);
                                if($this->nodes->create_new_node($first_name, $node, $id, $parent_depth+1)){
                                    $node_id = $this->nodes->get_node_ownership($id);
                                    if($node_id){
                                        if($this->node_members->set_node_membership($id, $node_id)){
                                            return true;
                                        }
                                        else{
                                            $GLOBALS['error_message'] = "Could not set node membership";
                                            return false;
                                        }
                                    }
                                }
                            }
                            else{
                                // Add to existing node
                                if($this->node_members->set_node_membership($id, $node)){
                                    if($user_level > 1){
                                        // Request to reasign node ownership
                                        if($new_owner){
                                            if($this->nodes->set_node_owner($node, $id)){
                                                return true;
                                            }
                                            else{
                                                $GLOBALS['error_message'] = "Failed to update node ownership";
                                            }
                                        }
                                        else{
                                            // Reasign node ownership if node is orphaned
                                            $owner_id = $this->nodes->get_node_owner($node);
                                            if(!$this->accounts->check_account_id($owner_id)){
                                                if($this->nodes->set_node_owner($node, $id)){
                                                    return true;
                                                }
                                                else{
                                                    $GLOBALS['error_message'] = "Failed to update node ownership";
                                                }
                                            }
                                        }
                                    }
                                    else{
                                        // Request to reasign node ownership
                                        if($new_owner){
                                            if($this->nodes->set_node_owner($node, $id)){
                                                return true;
                                            }
                                            else{
                                                $GLOBALS['error_message'] = "Failed to update node ownership";
                                            }
                                        }
                                    }
                                    return true;
                                }
                                else{
                                    $GLOBALS['error_message'] = "Could not set node membership";
                                    return false;
                                }
                            }                      
                        }
                    }
                    else{
                        $GLOBALS['error_message'] = "Could not get user ID";
                        return false;
                    }            
                }
                else{
                    $GLOBALS['error_message'] = "Bad SQL Query: Create New Account";
                    return false;
                }
            }
            else{
                $GLOBALS['error_message'] = "Username not available";
                return false;
            }
        }
        else{
            $GLOBALS['error_message'] = "Invalid character used in either first, last, or user name. Only use ('.', '-', '_')" ;
            return false;
        }
    }

    // CONVENIENCE: Deletes user account, node membership, and node (if empty)
    //--$user_id: 'id' value of user from accounts table (INT)
    // RETURN: boolean (! No error checking)
    public function delete_account($user_id){
        $node_id = $this->node_members->get_node_membership($user_id);
        $count = $this->node_members->get_node_client_count($node_id);
        if($count === 1){
            $this->nodes->delete_node($node_id);
        }
        $this->accounts->delete_user_account($user_id);
        $this->node_members->delete_node_membership($user_id);
        $this->birthdays->delete_user_birthday($user_id);
        return true;
    }


  ///////////////////////////////////////////
 ////  CONVENIENCE FUNCTIONS  //////////////
///////////////////////////////////////////


    // Provides a list of labels for user privilages
    //--$i: value representing the user level (INT)
    //-- RETURN: string
    public function account_access_label($i){
        switch ($i) {
            case '1':
                return "User";
            case '2':
                return "Node Admin";
            case '3':
                return "Server Admin";
            default:
                return "Blocked";
        }
    }

    // Convert mysqli result array into ID indexed assoc array
	//-- $function: function pointer
    //-- $index_name: unique array key used to sort results array
    //-- RETURN: sorted array used by $membership
	private function get_db_array($function, $index_name){
		$result_array = $function();
		array_push($this->page_data, $result_array);
		while($row = $result_array->fetch_assoc()){
			$result[$row[$index_name]] = $row;
		}
		return $result;
    }
    
    // Sets the first letter of a name to uppercase
    //--$string: value entered by user for name
    //-- RETURN: modified string with initial capital
    public function ucname($string){
        $string =ucwords(strtolower($string));
    
        foreach (array('-', '\'') as $delimiter) {
          if (strpos($string, $delimiter)!==false) {
            $string =implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));
          }
        }
        return $string;
    }

    // Checks name for valid characters
    //--$str: string to be checked
    //-- RETURN: boolean
    public function validate_name($str){
        $allowed = array(".", "-", "_", " "); // you can add here more value, you want to allow.
        if(ctype_alnum(str_replace($allowed, '', $str ))) {
            return true;
        }
        else{
            return false;
        }
    }

// Checks to make sure email address matches standard pattern
    //-- $email - string
    //-- RETURN: boolean
    public function validate_email($email){
        $pattern = '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD';
        if(preg_match($pattern, $email)){
          return true;
        }
        else{
          return false;
        }
    }
  
    public function validate_date_format($date){
        $pattern = '/^(19|20)\d\d([-])(0[1-9]|1[012])\2(0[1-9]|[12][0-9]|3[01])$/';
        if(preg_match($pattern, $date)){
          return true;
        }
        else{
          return false;
        }
    }
  
    public function validate_date($date, $format = 'Y-m-d')
    {
          date_default_timezone_set('America/Dawson_Creek');
  
          $formated_date = DateTime::createFromFormat($format, $date);
          // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
          return $formated_date && $formated_date->format($format) === $date && strtotime($date) < time();
    }

    public function validate_birthday($date){
        return $this->validate_date_format($date) && $this->validate_date($date);
    }

    // Sorts an array by its primary key ('id')
    //--$array: list of int values
    //-- RETURN: sorted arrray. Used by $nodes_id
    private function sort_array_by_id($array){
		foreach($array as $row){
			$temp[$row['id']] = $row;
		}
		return $temp;
    }
    
    // Sorts a array of node_members data by key ('node_id')
    //--$members_array
    //-- RETURN: returns a sorted array. Used by $client_tally
    public function count_node_members($members_array){
		$client_tally = array();
		
		foreach($members_array as $user){
			$key = $user['node_id'];
			if(array_key_exists($key, $client_tally)){
				$client_tally[$key] += 1;
			}
			else{
				$client_tally[$key] = 1;
			}
			
		}
		return $client_tally;
    }
    
    // Prints an array with formatting
    //--$array: array to be printed
    //-- RETURN: HTML string. Diagnostic
    public function printArray($array){
        echo "<pre>";
        print_r($array);
        echo "</pre>";
    }

    // Checks a password for minimum requirements
    //-- $password - string
    //-- RETURN: boolean
    public function check_password_strength($password){
        // Validate password strength
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number    = preg_match('@[0-9]@', $password);
        $specialChars = preg_match('@[^\w]@', $password);

        if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
            $GLOBALS['error_message'] = 'Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character.';
            return false;
        }else{
            return true;
        }
    }

    function __destruct(){
        foreach($this->page_data as $var){
			$var->free();
		}
		$this->connection->close();
    }
}
?>