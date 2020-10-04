<?php
// Prepared statements: https://websitebeaver.com/prepared-statements-in-php-mysqli-to-prevent-sql-injection

// ==== Sessions ====
// [1]  - (I) create_websocket_session($uuid, $ip_addr)      R=> bool


  ///////////////////////////////////////////
 ////  TABLE: SESSIONS  ////////////////////
///////////////////////////////////////////

class Sessions{
    private $connection;    // MySQLi DB connection

    function __construct($connection){
        $this->connection = $connection;
    }

    // [1]
    // INSERT: Creates a new session entry in sessions Table
    //-- $uuid: pseudo-random hex string to use as unique identifier (like UUIDv4) (STR)
    //-- $ip_addr: ip address of the client requesting the session (STR)
    // RETURN: boolean
    public function create_websocket_session($uuid, $ip_addr){
        if ($stmt = $this->connection->prepare('INSERT INTO sessions(uuid, ip_addr) VALUES (?, ?)')) {
            // Bind parameters (s = string, i = int, b = blob, d = double), in our case the username is a string so we use "s"
            $stmt->bind_param('ss', $uuid, $ip_addr);
            $status = $stmt->execute();
            $stmt->close();
            if($status === false){
                $GLOBALS['error_message'] = "SQL INSERT FAILED: [Sessions] Create Websocket Session";
                return false;
            }            
            return true;
        }
        else{
            $GLOBALS['error_message'] = "Bad SQL Query: [Sessions] Create Websocket Session";
            return false;
        }
    }
}

?>