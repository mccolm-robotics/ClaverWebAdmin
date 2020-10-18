<?php

// ==== Node Devices ====
// [1]  - (S) get_node_devices($node_id)            R=> assoc array
// [2]  - (U) set_status($status, $device_id)       R=> bool
// [3]  - (S) get_id_for_device($device_id)         R=> int
// [4]  - (D) delete_device($id)                    R=> bool


  ///////////////////////////////////////////
 ////  TABLE: NODE DEVICES  ////////////////
///////////////////////////////////////////

class Node_Devices{
    private $connection;

    function __construct($connection){
        $this->connection = $connection;
    }

    // [1]
    // SELECT: Gets all devices for a specific node from node_devices table as array
    //-- $node_id: int value corresponding to the 'id' value for node in nodes table
    // RETURN: array of vals
    public function get_node_devices($node_id) {
        if ($stmt = $this->connection->prepare('SELECT id, device_id, platform, device_name, status, last_reconnect FROM node_devices WHERE node_id = ?')) {
            $stmt->bind_param('i', $node_id);
            $stmt->execute();
            $result = $stmt->get_result(); // get the mysqli result
            $devices_list = $result->fetch_all(MYSQLI_ASSOC); // fetch data  
            $stmt->close();
            return $devices_list;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Node_Devices] Get Devices List";
            return false;
        }
    }

    // [2]
    // UPDATE: Set the 'status' and 'last_reconnect' columns for device with the specified 'device_id'
    //-- $status: A boolean indicating the device status. A positive value shows the device is online. Stored in the 'status' value.
    //-- $device_id: The 'device_id' value associated with a node device. This is a unique value.
    // RETURN: (bool)
    public function set_status($status, $device_id){
        if ($stmt = $this->connection->prepare('UPDATE node_devices SET last_reconnect=?, status=? WHERE device_id = ?')) {
            $time = time();
            $stmt->bind_param('iis', $time, $status, $device_id);
            $status = $stmt->execute();
            $stmt->close();
            if($status === false){
                $GLOBALS['error_message'] = "SQL UPDATE FAILED: [Node_Devices] Set Status";
                return false;
            }            
            return true;
        }
        else {
            $GLOBALS['error_message'] = "Bad SQL Query: [Node_Devices] Set Status";
            return false;
        }
    }

    // [3]
    // SELECT: Gets the id of an authorized device according to serial number
    //-- $device_id: The serial number (string) of an authorized device
    // RETURN: 'id' value of device from node_devices table. (INT)
    public function get_id_for_device($device_id){
        if ($stmt = $this->connection->prepare('SELECT id FROM node_devices WHERE device_id = ?')) {
            // Bind parameters (s = string, i = int, b = blob, etc)
            $stmt->bind_param('s', $device_id);
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
            $GLOBALS['error_message'] = "Bad SQL Query: [Node_Devices] Get ID For Device";
            return false;
        }
    }

    // [4]
    // DELETE: Remove a device from the node_devices table
    //-- $id: 'id' value of device from node_devices table (INT)
    // RETURN: boolean
    public function delete_device($id){
        if ($stmt = $this->connection->prepare('DELETE FROM node_devices WHERE id = ?')) {
            $stmt->bind_param('i', $id);
            $status = $stmt->execute();
            $stmt->close();
            if($status === false){
                $GLOBALS['error_message'] = "SQL DELETE FAILED: [Node_Devices] Delete Device";
                return false;
            }            
            return true;
        }
        else{
            $GLOBALS['error_message'] = "Bad SQL Query: [Node_Devices] Delete Device";
            return false;
        }
    }
}

?>