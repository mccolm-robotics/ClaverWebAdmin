<?php
    if($_SESSION['user_level'] < 2){
        header('Location: account.php');
    }

    // Includes
    require_once 'db/nodes.php';
    require_once 'db/accounts.php';
    require_once 'db/node_members.php';
    require_once 'db/node_devices.php';
    require_once 'db/device_invitations.php';

    // Global variables for passing result messages
    $success_message = NULL;
    $error_message = NULL;


  ///////////////////////////////////////////
 ////  Node Management  ////////////////////
///////////////////////////////////////////

class Node_Management {
    private $connection;
    private $user_id;
    private $node_id;
    private $node_name;
    private $node_alias;
    private $authorized_devices;
    public $node_clients;
    public $node_owner_id;

    private $good_id = true;
    private $nodes;
    private $accounts;
    private $node_members;
    private $node_devices;
    private $device_invitations;
    private $page_data = array(); // A list of arrays with data created for this page

    function __construct($connection, $node_id){
        $this->connection = $connection;

        $this->nodes = new Nodes($connection);
        $this->accounts = new Accounts($connection);
        $this->node_members = new Node_Members($connection);
        $this->node_devices = new Node_Devices($connection);
        $this->device_invitations = new Device_Invitations($connection);

        if($node_id == NULL){
            $this->node_id = $this->node_members->get_node_membership($_SESSION['id']);
        }
        else{
            $this->node_id = $node_id;
        }

        // Discover user's node id
        $this->node_owner_id = $this->nodes->get_node_owner($this->node_id);
        if(!$this->node_owner_id){
            $this->good_id = false;
        }

        // Get a list of other users belonging to this node
        $members = $this->node_members->get_clients_list($this->node_id);
        foreach($members as $client){
            $this->node_clients[$client['user_id']] = $this->accounts->get_full_name($client['user_id']);
        }

        // Get a list of connected Claver devices
        $devices = $this->node_devices->get_node_devices($this->node_id);
        foreach($devices as $item){
            $this->authorized_devices[$item['id']] = $item;
        }


        // Get the name and alias for this node
        if($this->node_id){
            $this->node_name = $this->nodes->get_node_name($this->node_id);
            $this->node_alias = $this->nodes->get_node_alias($this->node_id);
        }
    }

    public function set_user_level($user_level, $user_id){
        if($user_level >= 3 && $_SESSION['user_level'] < 3){
            $GLOBALS['error_message'] = "You do not have the necessary permission to assign this user-level";
            return false;
        }
        else{
            return $this->accounts->set_user_level($user_level, $user_id);
        }
    }

    public function get_device_invitations(){
        $result = $this->device_invitations->get_registered_devices($this->node_id);
        foreach($result as $item){
            $devices[$item['id']] = $item;
        }
        if(!empty($devices)){
            return $devices;
        }
        else{
            return False;
        }
    }

    public function create_device_invitation($device_id, $user_id){
        $expires = time() + 900;
        $is_valid = True;
        return $this->device_invitations->add_invitation($device_id, $user_id, $this->node_id, $expires, $is_valid);
    }

    public function set_device_status($status, $device_id){
        return $this->node_devices->set_status($status, $device_id);
    }

    public function get_authorized_devices(){
        return $this->authorized_devices;
    }

    public function get_id_for_device($device_id){
        return $this->node_devices->get_id_for_device($device_id);
    }

    public function get_user_level($user_id){
        return $this->accounts->get_user_level($user_id);
    }

    public function get_user_level_name($user_id){
        return $this->account_access_label($this->accounts->get_user_level($user_id));
    }

    public function confirm_node_id(){
        return $this->good_id;
    }

    public function get_client_list(){
        return $this->node_clients;
    }

    public function get_node_name(){
        return $this->node_name;
    }

    public function get_node_alias(){
        return $this->node_alias;
    }

    public function get_node_id(){
        return $this->node_id;
    }

    public function set_alias($node_alias){
        if($this->validate_name($node_alias)){
            $node_alias = $this->checkAmpersand($node_alias);
            $node_alias = $this->ucname($node_alias);
            if($this->nodes->set_node_alias($node_alias, $this->node_id)){
                $this->node_alias = $node_alias;
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }

    public function clear_alias(){
        $this->nodes->set_node_alias("", $this->node_id);
        $this->node_alias = NULL;
    }

    public function transfer_owner($user_id){
        $this->node_owner_id = $user_id;
        $new_owners_current_user_level = $this->get_user_level($user_id);
        if($new_owners_current_user_level == 1){
            $this->set_user_level(2, $user_id);
        }
        return $this->nodes->set_node_owner($this->node_id, $user_id);
    }



// Settings: 
//--> Restore recommended

// Create an invitation to join node as client or sub-node
//--> Set client user privilages

// Create Channels
//--> Household sharing node (pictures on the fridge type sharing)
//--> Global channel


// Set sharing permissions
//--> per user
//--> per node
//--> Allow muting per node
//--> Allow passthrough per node -> transmissions from child nodes are presented as parent nodes with a 'child' tag.




  ///////////////////////////////////////////
 ////  CONVENIENCE FUNCTIONS  //////////////
///////////////////////////////////////////

    public function elapsed_time($timestamp){
        $running_time = "";
        $num_days = intdiv((time() - $timestamp), 86400);
        $num_hours = intdiv((time() - $timestamp), 3200);
        $num_min = intdiv((time() - $timestamp), 60);
        if($num_days || $num_hours || $num_min){
            if(!$num_days){
                if(!$num_hours){
                    if($num_min > 1){
                        $running_time = $num_min." minutes";
                    }
                    else{
                        $running_time = $num_min." minute";
                    }
                }
                else{
                    if($num_hours > 1){
                        $running_time = $num_hours." hours";
                    }
                    else{
                        $running_time = $num_hours." hour";
                    }
                }
            }
            else{
                if($num_days > 1){
                    $running_time = $num_days." days";
                }
                else{
                    $running_time = $num_days." day";
                }
            }
        }
        else{
            $running_time = (time() - $timestamp)." sec";
        }
        return $running_time;
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

    private function checkAmpersand($str){
        // Convert 'and' into '&'
        $pattern = "/\s(and)\s/";
        if(preg_match_all($pattern, $str)){    
            $str = preg_replace($pattern, " & ", $str);
        }

        // Strip out leading spaces
        $pattern = "/^\s+/m";
        if(preg_match_all($pattern, $str)){    
            $str = preg_replace($pattern, "", $str);
        }

        // Strip out trailing spaces
        $pattern = "/\s+$/m";
        if(preg_match_all($pattern, $str)){    
            $str = preg_replace($pattern, "", $str);
        }

        // Strip out multiple spaces
        $pattern = "/\s\s+/";
        if(preg_match_all($pattern, $str)){    
            $str = preg_replace($pattern, " ", $str);
        }

        // Strip leading &
        $pattern = "/^&+/m";
        if(preg_match_all($pattern, $str)){
            $str = preg_replace("/^&+/", "", $str);
        }

        // Strip trailing &
        $pattern = "/&+$/m";
        if(preg_match_all($pattern, $str)){
            $str = preg_replace("/&+$/", "", $str);
        }

        // Find any & touching another char on its left side
        $pattern = "/(.[^\s])&(.)/";
        if(preg_match_all($pattern, $str)){
            $str = preg_replace("/&/", " &", $str);
        }

        // Find any & touching another char on its right side
        $pattern = "/(.)&(.[^\s])/";
        if(preg_match_all($pattern, $str)){
            $str = preg_replace("/&/", "& ", $str);
        }

        return $str;
    }

    // Checks name for valid characters
    //--$str: string to be checked
    //-- RETURN: boolean
    private function validate_name($str){
        $allowed = array(".", "-", "_", " ", "&"); // you can add here other values you want to allow.
        if(ctype_alnum(str_replace($allowed, '', $str ))) {
            return true;
        }
        else{
            return false;
        }
    }

    // Prints an array with formatting
    //-- RETURN: HTML string. Diagnostic
    public function printArray($array){
        echo "<pre>";
        print_r($array);
        echo "</pre>";
    }

    function __destruct(){
        foreach($this->page_data as $var){
			$var->free();
		}
		$this->connection->close();
    }
}
    ?>