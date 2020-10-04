<?php
// Prepared statements: https://websitebeaver.com/prepared-statements-in-php-mysqli-to-prevent-sql-injection

// ==== Nodes ====
// [1]  - (S) get_all_nodes()                R=> MySQLi result obj
// [2]  - (I) create_new_node($node_name, $child_of, $node_owner, $depth=1)      R=> bool
// [3]  - (S) get_node_ownership($user_id)   R=> int
// [4]  - (S) get_node_owner($node_id)       R=> int
// [5]  - (U) set_node_owner($node_id, $user_id)     R=> bool
// [6]  - (S) get_node_depth($child_of)      R=> int
// [7]  - (D) delete_node($node_id)          R=> bool
// [8]  - (S) get_node_name($node_id)        R=> string
// [9]  - (S) get_node_alias($node_id)       R=> string
// [10] - (U) set_node_alias($node_alias, $node_id)     R=> bool


  ///////////////////////////////////////////
 ////  TABLE: NODES  ///////////////////////
///////////////////////////////////////////

class Nodes{
    private $connection;    // MySQLi DB connection

    function __construct($connection){
        $this->connection = $connection;
    }

    // [1]
    // SELECT: Gets all information from nodes DB
    // RETURN: array of values
    public function get_all_nodes() {      
        $sql = "SELECT * FROM nodes";
        $result = $this->connection->query($sql);
        
        if ($result->num_rows > 0) {
        return $result;
        }
        else{
            return NULL;
        }
    }

    // [2]
    // INSERT: Creates a new node entry in nodes Table
    //-- $node_name: name of the node. Derived from the initial account user's first name (STR)
    //-- $child_of: the 'id' value of an existing node in nodes table (INT)
    //-- $node_owner: the 'id' value of a user from accounts table (INT)
    //-- $depth: represents a nodes position in the node tree. -(INT)
    // RETURN: boolean
    public function create_new_node($node_name, $child_of, $node_owner, $depth=1){
        if ($stmt = $this->connection->prepare('INSERT INTO nodes(node_name, child_of, node_owner, depth) VALUES (?, ?, ?, ?)')) {
            // Bind parameters (s = string, i = int, b = blob, d = double), in our case the username is a string so we use "s"
            $stmt->bind_param('siii', $node_name, $child_of, $node_owner, $depth);
            $stmt->execute();
            $stmt->close();
            return true;
        }
        else{
            $GLOBALS['error_message'] = "Bad SQL Query: [Nodes] Create New Node";
            return false;
        }
    }

    // [3]
    // SELECT: Get an 'id' PK from nodes table using an accounts 'id' PK
    //-- $user_id: 'id' value of user from accounts table (INT)
    // RETURN: (INT)
    public function get_node_ownership($user_id){
        if ($stmt = $this->connection->prepare('SELECT id FROM nodes WHERE node_owner = ?')) {
            // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
            $stmt->bind_param('i', $user_id);
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
            $GLOBALS['error_message'] = "Bad SQL Query: [Nodes] Get Node Memebership";
            return false;
        }
    }

    // [4]
    // SELECT: Get an accounts 'id' PK from nodes table using a nodes 'id' PK
    //-- $node_id: 'id' value of node from nodes table (INT)
    // RETURN: (INT)
    public function get_node_owner($node_id){
        if ($stmt = $this->connection->prepare('SELECT node_owner FROM nodes WHERE id = ?')) {
            // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
            $stmt->bind_param('i', $node_id);
            $stmt->execute();
            // Store the result so we can check if the account exists in the database.
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($owner_id);
                $stmt->fetch();                
            } else {
                return false;
            }
            $stmt->close();
            return $owner_id;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Nodes] Get Node Owner";
            return false;
        }
    }

    // [5]
    // UPDATE: Assign a node's ownership to a different user.
    //-- $node_id: 'id' value of node from nodes table (INT)
    //-- $user_id: 'id' value of user from accounts table (INT)
    // RETURN: boolean
    public function set_node_owner($node_id, $user_id){
        if ($stmt = $this->connection->prepare('UPDATE nodes SET node_owner=? WHERE id=?')) {
            // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
            $stmt->bind_param('ii', $user_id, $node_id);
            $status = $stmt->execute();
            // Store the result so we can check if the account exists in the database.
            $stmt->close();
            if($status === false){
                $GLOBALS['error_message'] = "SQL UPDATE FAILED: [Nodes] Set Node Owner";
                return false;
            }            
            return true;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Nodes] Set Node Owner";
            return false;
        }
    }

    // [6]
    // SELECT: Get the 'depth' value of a node using a nodes 'id' PK
    //-- $child_of: the 'id' value of an existing node in nodes table (INT)
    // RETURN: (INT)
    public function get_node_depth($child_of){
        if ($stmt = $this->connection->prepare('SELECT depth FROM nodes WHERE id = ?')) {
            // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
            $stmt->bind_param('i', $child_of);
            $stmt->execute();
            // Store the result so we can check if the account exists in the database.
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($parent_depth);
                $stmt->fetch();                
            } else {
                return false;
            }
            $stmt->close();
            return $parent_depth;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Nodes] Get Node Depth";
            return false;
        }
    }

    // [7]
    // DELETE: Remove a node from the nodes table
    //-- $node_id: 'id' value of node from nodes table (INT)
    // RETURN: boolean
    public function delete_node($node_id){
        if ($stmt = $this->connection->prepare('DELETE FROM nodes WHERE id = ?')) {
            $stmt->bind_param('i', $node_id);
            $status = $stmt->execute();
            $stmt->close();
            if($status === false){
                $GLOBALS['error_message'] = "SQL DELETE FAILED: [Nodes] Delete Node";
                return false;
            }            
            return true;
        }
        else{
            $GLOBALS['error_message'] = "Bad SQL Query: [Nodes] Delete Node";
            return false;
        }
    }

    // [8]
    // SELECT: Get the 'node_name' value of a node using a node_id
    //-- $node_id: the 'id' value of an existing node in nodes table (INT)
    // RETURN: (STR)
    public function get_node_name($node_id){
        if ($stmt = $this->connection->prepare('SELECT node_name FROM nodes WHERE id = ?')) {
            // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
            $stmt->bind_param('i', $node_id);
            $stmt->execute();
            // Store the result so we can check if the account exists in the database.
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($node_name);
                $stmt->fetch();                
            } else {
                return false;
            }
            $stmt->close();
            return $node_name;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Nodes] Get Node Name";
            return false;
        }
    }

    // [9]
    // SELECT: Get the 'node_alias' value of a node using a node_id
    //-- $node_id: the 'id' value of an existing node in nodes table (INT)
    // RETURN: (STR)
    public function get_node_alias($node_id){
        if ($stmt = $this->connection->prepare('SELECT alias FROM nodes WHERE id = ?')) {
            // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
            $stmt->bind_param('i', $node_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows === 0){
                $stmt->close();
                return false;
            }
            $data = $result->fetch_assoc();
            $alias = $data['alias'];
            $stmt->close();
            if($alias == NULL){
                return false;
            }
            return $alias;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Nodes] Get Node Alias";
            return false;
        }
    }

    // [10]
    // UPDATE: Get the 'node_alias' value of a node using a node_id
    //-- $node_alias: the 'alias' value for an existing nodes row (STRING)
    //-- $node_id: the 'id' value of an existing node in nodes table (INT)
    // RETURN: (bool)
    public function set_node_alias($node_alias, $node_id){
        if ($stmt = $this->connection->prepare('UPDATE nodes SET alias=? WHERE id=?')) {
            // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
            $node_alias = ($node_alias == "")? NULL : $node_alias;
            $stmt->bind_param('si', $node_alias, $node_id);
            $status = $stmt->execute();
            // Store the result so we can check if the account exists in the database.
            $stmt->close();
            if($status === false){
                $GLOBALS['error_message'] = "SQL UPDATE FAILED: [Nodes] Set Node Alias";
                return false;
            }            
            return true;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Nodes] Set Node Alias";
            return false;
        }
    }
}

?>