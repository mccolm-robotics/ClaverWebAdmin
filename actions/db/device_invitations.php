<?php

// ==== Device Invitations ====
// [1]  - (I) add_invitation($device_id, $user_id, $node_id, $expires, $is_valid)            R=> bool
// [2]  - (S) get_registered_devices($node_id)      R=> Assoc Array
// [3]  - (D) delete_invitation($invitation_id)     R=> bool


  ///////////////////////////////////////////
 ////  TABLE: Device Invitations  //////////
///////////////////////////////////////////

class Device_Invitations{
    private $connection;

    function __construct($connection){
        $this->connection = $connection;
    }

    // [1]
    // INSERT: Add a device to the device_invitations table
    //-- $device_id: Serial number of the device (STR)
    //-- $user_id: 'id' of user who created invitation (INT)
    //-- $node_id: 'id' of the node owned associated with the user who created invitation (INT)
    //-- $expires: Unix Timestamp 15 minutes ahead of the moment that the invitation was created (INT)
    //-- $is_valid: A boolean value indicating whether the invitation has been accepted. A value of zero indicates the invitation has been used. (INT)
    // RETURN: boolean
    public function add_invitation($device_id, $user_id, $node_id, $expires, $is_valid, $access_code){
        if ($stmt = $this->connection->prepare('INSERT INTO device_invitations(device_id, user_id, node_id, expires, is_valid, access_code) VALUES (?, ?, ?, ?, ?, ?)')) {
            $stmt->bind_param('siiiis', $device_id, $user_id, $node_id, $expires, $is_valid, $access_code);
            $status = $stmt->execute();
            $stmt->close();
            if($status === false){
                $GLOBALS['error_message'] = "SQL INSERT FAILED: [Device_Invitations] Add Invitation";
                return false;
            }            
            return true;
        }
        else{
            $GLOBALS['error_message'] = "Bad SQL Query: [Device_Invitations] Add Invitation";
            return false;
        }
    }

    // [2]
    // SELECT: Gets all devices for a specific node from device_invitations table as array
    //-- $node_id: int value corresponding to the 'id' value for node in nodes table
    // RETURN: array of vals
    public function get_registered_devices($node_id){
        if ($stmt = $this->connection->prepare('SELECT id, device_id, expires, is_valid, access_code FROM device_invitations WHERE node_id = ?')) {
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

    // [3]
    // DELETE: Remove a device from the device_invitations table
    //-- $invitation_id: 'id' value of device invitation from device_invitations table (INT)
    // RETURN: boolean
    public function delete_invitation($invitation_id){
        if ($stmt = $this->connection->prepare('DELETE FROM device_invitations WHERE id = ?')) {
            $stmt->bind_param('i', $invitation_id);
            $status = $stmt->execute();
            $stmt->close();
            if($status === false){
                $GLOBALS['error_message'] = "SQL DELETE FAILED: [Device_Invitations] Delete Invitation";
                return false;
            }            
            return true;
        }
        else{
            $GLOBALS['error_message'] = "Bad SQL Query: [Device_Invitations] Delete Invitation";
            return false;
        }
    }
}

?>