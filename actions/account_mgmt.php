<?php

    // Includes
    require_once 'db/nodes.php';
    require_once 'db/accounts.php';
    require_once 'db/node_members.php';
    require_once 'db/birthdays.php';

    // Global variables for passing result messages
    $success_message = NULL;
    $error_message = NULL;


  ///////////////////////////////////////////
 ////  Node Management  ////////////////////
///////////////////////////////////////////

class Account_Management {
    private $connection;
    private $user_id;
    private $user_name;
    private $node_id;
    private $user_data;
    private $username;
    private $password;
    private $first_name;
    private $last_name;
    private $email;
    private $birthday;
    private $good_id = true;

    private $nodes;
    private $accounts;
    private $node_members;
    private $birthdays;
    private $page_data = array(); // A list of arrays with data created for this page

    function __construct($connection, $user_id=NULL){
        $this->connection = $connection;
        
        $this->nodes = new Nodes($connection);
        $this->accounts = new Accounts($connection);
        $this->node_members = new Node_Members($connection);
        $this->birthdays = new Birthdays($connection);

        if($user_id == NULL){
          $this->user_id = $_SESSION['id'];
        }
        else{
          if($this->accounts->check_account_id($user_id)){
            $this->user_id = $user_id;
          }
          else{
            $this->good_id = false;
          }
        }

        // Account information
        $this->user_data = $this->accounts->get_user_data($this->user_id);
        $this->username = $this->user_data['username'];
        $this->password = $this->user_data['password'];
        $this->email = $this->user_data['email'];
        $this->first_name = $this->user_data['first_name'];
        $this->last_name = $this->user_data['last_name'];
        $this->birthday = $this->birthdays->get_birthday($this->user_id);


        // Node information
        $this->node_id = $this->node_members->get_node_membership($this->user_id);
        if($this->node_id){
            $this->node_name = $this->nodes->get_node_name($this->node_id);
        }
    }

    public function get_birthday(){
      return $this->birthday;
    }

    public function set_birthday($date){
      if(!$this->birthday){
        if($this->birthdays->create_user_birthday($this->user_id, $this->first_name, date("n", strtotime($date)), date("j", strtotime($date)), $date)){
          $this->birthday = $date;
          return true;
        }
        else{
          return false;
        }
      }
      else{
        if($this->birthdays->set_birthday(date("n", strtotime($date)), date("j", strtotime($date)), $date, $this->user_id)){
          $this->birthday = $date;
          return true;
        }
        else{
          return false;
        }
      }
    }

    public function get_user_id(){
      return $this->user_id;
    }

    public function get_node_name(){
        return $this->node_name;
    }

    public function get_username(){
      return $this->username;
    }

    public function get_password(){
      return $this->password;
    }

    public function get_first_name(){
      return $this->first_name;
    }

    public function get_last_name(){
      return $this->last_name;
    }

    public function get_email(){
      return $this->email;
    }

    public function set_password($password){
      return $this->accounts->set_password($this->user_id, $password);
    }

    public function set_email($email){
      if($this->accounts->set_email($email, $this->user_id)){
        $this->email = $email;
        return true;
      }
      return false;
    }

    public function confirm_account_id(){
      return $this->good_id;
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

    function validate_date($date, $format = 'Y-m-d')
    {
        date_default_timezone_set('America/Dawson_Creek');

        $formated_date = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $formated_date && $formated_date->format($format) === $date && strtotime($date) < time();
    }

    // Prints an array with formatting
    //-- RETURN: HTML string. Diagnostic
    function printArray($array){
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