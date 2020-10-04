<?php

// ==== Node Members ====
// [1] - (S) get_all_members()      R=> MySQLi result obj
// [2] - (D) delete_node_membership($user_id)   R=>
// [3] - (I) set_node_membership($user_id, $node_id)    R=>
// [4] - (S) get_node_membership($user_id)      R=>
// [5] - (S) get_node_client_count($node_id)    R=>
// [6] - (S) get_clients_list()    R=> array of vals


  ///////////////////////////////////////////
 ////  TABLE: NODE MEMBERS  ////////////////
///////////////////////////////////////////

class Node_Members{
    private $connection;

    function __construct($connection){
        $this->connection = $connection;
    }

    // [1]
    // SELECT: Gets all data from the node_members table
    // RETURN: array of vals
    public function get_all_members() {      
        $sql = "SELECT * FROM node_members";
        $result = $this->connection->query($sql);
        
        if ($result->num_rows > 0) {
        return $result;
        }
        else{
            return NULL;
        }
    }

    // [2]
    // DELETE: Remove a user from the node_members table
    //-- $user_id: 'id' value of user from accounts table (INT)
    // RETURN: boolean
    public function delete_node_membership($user_id){
        if ($stmt = $this->connection->prepare('DELETE FROM node_members WHERE user_id = ?')) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->close();
            return true;
        }
        else{
            $GLOBALS['error_message'] = "Bad SQL Query: [Node_Members] Delete Node Membership";
            return false;
        }
    }

    // [3]
    // INSERT: Add a new user to the node_members table 
    //-- $user_id: 'id' value of user from accounts table (INT)
    //-- $node_id: 'id' value of node from nodes table (INT)
    // RETURN: boolean
    public function set_node_membership($user_id, $node_id){
        if ($stmt = $this->connection->prepare('INSERT INTO node_members(user_id, node_id) VALUES (?, ?)')) {
            // Bind parameters (s = string, i = int, b = blob, d = double), in our case the username is a string so we use "s"
            $stmt->bind_param('ii', $user_id, $node_id);
            $stmt->execute();
            $stmt->close();
            return true;
        }
        else{
            $GLOBALS['error_message'] = "Bad SQL Query: [Node_Members] Set Node Membership";
            return false;
        }
    }

    // [4]
    // SELECT: Get a nodes 'id' PK from node_members table using 'id' PK from accounts table
    //-- $user_id: 'id' value of user from accounts table (INT)
    // RETURN: (INT)
    public function get_node_membership($user_id){
        if ($stmt = $this->connection->prepare('SELECT node_id FROM node_members WHERE user_id = ?')) {
            // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            // Store the result so we can check if the account exists in the database.
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($node_id);
                $stmt->fetch();                
            } else {
                $stmt->close();
                return false;
            }
            $stmt->close();
            return $node_id;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Node_Members] Get Node Depth";
            return false;
        }
    }

    // [5]
    // SELECT: Gets the 'id' PK value from node_members table using an 'id' value from the nodes table 
    //-- $node_id: 'id' value of node from nodes table (INT)
    // RETURN: a count of table rows (INT)
    public function get_node_client_count($node_id){
        if ($stmt = $this->connection->prepare('SELECT id FROM node_members WHERE node_id = ?')) {
            // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
            $stmt->bind_param('i', $node_id);
            $stmt->execute();
            $result = $stmt->get_result(); // get the mysqli result
            $user = $result->fetch_all(); // fetch data  
            $stmt->close();
            return count($user);
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Node_Members] Get Account ID";
            return false;
        }
    }

    // [6]
    // SELECT: Gets all clients of specific node from node_members table as array
    //-- $node_id: int value corresponding to the 'id' value for node in nodes table
    // RETURN: array of vals
    public function get_clients_list($node_id) {
        if ($stmt = $this->connection->prepare('SELECT user_id FROM node_members WHERE node_id = ?')) {
            $stmt->bind_param('i', $node_id);
            $stmt->execute();
            $result = $stmt->get_result(); // get the mysqli result
            $clients_list = $result->fetch_all(MYSQLI_ASSOC); // fetch data  
            $stmt->close();
            return $clients_list;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Node_Members] Get Account ID";
            return false;
        }
    }
}

?>