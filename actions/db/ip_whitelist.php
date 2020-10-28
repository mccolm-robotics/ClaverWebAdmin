<?php

// ==== IP Whitelist ====
// [1]  - (I) add_address($ip_address, $user_id)            R=> bool


  ///////////////////////////////////////////
 ////  TABLE: IP Whitelist /////////////////
///////////////////////////////////////////

class IP_Whitelist{
    private $connection;

    function __construct($connection){
        $this->connection = $connection;
    }

    // [1]
    // INSERT: Add a device IP to the ip_whitelist table
    //-- $address: IP address of the claver board (STR)
    //-- $user_id: 'id' of user who created invitation (INT)
    // RETURN: boolean
    public function add_address($ip_address, $user_id){
        if ($stmt = $this->connection->prepare('INSERT INTO ip_whitelist(address, user_id) VALUES (?, ?)')) {
            $stmt->bind_param('si', $ip_address, $user_id);
            $status = $stmt->execute();
            $stmt->close();
            if($status === false){
                $GLOBALS['error_message'] = "SQL INSERT FAILED: [IP_Whitelist] Add Address";
                return false;
            }            
            return true;
        }
        else{
            $GLOBALS['error_message'] = "Bad SQL Query: [IP_Whitelist] Add Address";
            return false;
        }
    }

    // [2]
    // SELECT: Gets all IP addresses from ip_whitelist matching ip_address
    //-- $ip_address: string value representing the IP address to compare against table entries
    // RETURN: array of vals
    public function get_existing_address($ip_address){
        if ($stmt = $this->connection->prepare('SELECT id FROM ip_whitelist WHERE address = ?')) {
            $stmt->bind_param('s', $ip_address);
            $status = $stmt->execute();
            if($status === false){
                $GLOBALS['error_message'] = "SQL SELECT FAILED: [IP_Whitelist] Get Existing Address";
                return false;
            } 
            $result = $stmt->get_result(); // get the mysqli result
            $address_list = $result->fetch_all(MYSQLI_ASSOC); // fetch data  
            $stmt->close();
            return $address_list;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [IP_Whitelist] Get Existing Address";
            return false;
        }
    }
}

?>