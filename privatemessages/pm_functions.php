<?php
/*****************  Private message functions *******************/

/**
 * Load data for all users.
 * @todo also load group membership
 * @param int $limit (optional) the maximum number of users to return.
 * @return object $results fetch non-authorization related data for the all users
 */
function loadPMS($limit = NULL, $user_id, $send_rec_id, $deleted){

    try {
        global $db_table_prefix;

        $results = array();

        $db = pdoConnect();

        $sqlVars = array();

        $query = "select {$db_table_prefix}plugin_pm.id as
        message_id, sender_id, receiver_id, title, message,
        time_sent, time_read, receiver_read, sender_deleted,
        receiver_deleted, isreply
        from {$db_table_prefix}plugin_pm
        WHERE $send_rec_id = :user_id AND $deleted != '1'";

        $stmt = $db->prepare($query);
        $sqlVars[':user_id'] = $user_id;
        $stmt->execute($sqlVars);

        if (!$limit){
            $limit = 9999999;
        }
        $i = 0;

        while ($r = $stmt->fetch(PDO::FETCH_ASSOC) and $i < $limit) {
            $id = $r['message_id'];
            $results[$id] = $r;
            $i++;
        }

        $stmt = null;
        return $results;

    } catch (PDOException $e) {
        addAlert("danger", "Oops, looks like our database encountered an error.");
        error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
        return false;
    }
}

function loadPMById($msg_id, $user_id){

    try {
        global $db_table_prefix;

        $results = array();

        $db = pdoConnect();

        $sqlVars = array();

        $query = "select {$db_table_prefix}plugin_pm.id as
        message_id, sender_id, receiver_id, title, message,
        time_sent, time_read, receiver_read, sender_deleted,
        receiver_deleted, isreply
        from {$db_table_prefix}plugin_pm
        WHERE id = :msg_id AND receiver_id OR sender_id = :user_id";

        $stmt = $db->prepare($query);
        $sqlVars[':msg_id'] = $msg_id;
        $sqlVars[':user_id'] = $user_id;
        $stmt->execute($sqlVars);

        //$stmt = $db->prepare($query);
        //$stmt->execute($sqlVars);

        //ChromePhp::log("Data: ".$sqlVars[':msg_id']);

        if (!($results = $stmt->fetch(PDO::FETCH_ASSOC))){
            addAlert("danger", "Invalid Message id specified");
            return false;
        }

        //ChromePhp::log($results);

        if($results['receiver_deleted'] != '0'){
            addAlert("danger", "Message Deleted");
            return false;
        }

        $stmt = null;

        checkPMReadFlag($msg_id);

        return $results;

    } catch (PDOException $e) {
        addAlert("danger", "Oops, looks like our database encountered an error.");
        error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
        return false;
    }
}

function loadPMReplys($msg_id){

    try {
        global $db_table_prefix;

        $results = array();

        $db = pdoConnect();

        $sqlVars = array();

        $query = "select {$db_table_prefix}plugin_pm.id as
        message_id, sender_id, receiver_id, title, message,
        time_sent, time_read, receiver_read, sender_deleted,
        receiver_deleted, isreply, parent_id
        from {$db_table_prefix}plugin_pm
        WHERE isreply = '1' AND parent_id = :msg_id";

        $stmt = $db->prepare($query);
        $sqlVars[':msg_id'] = $msg_id;
        //$sqlVars[':user_id'] = $user_id;
        $stmt->execute($sqlVars);
        $limit = '99999';

        //if (!($results = $stmt->fetch(PDO::FETCH_ASSOC))){
        //    addAlert("danger", "Invalid Message id specified");
        //    return false;
        //}

        $i = 0;
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            //echo '<pre>';
            //print_r($r);
            //echo '</pre>';
            //if($r['receiver_deleted'] || $r['sender_deleted'] != '1'){
                $id = $r['message_id'];
                $results[$id] = $r;
                $i++;
            //}
        }

        //if($results['receiver_deleted'] != '0'){
            //addAlert("danger", "Message Deleted");
        //    return false;
        //}

        $stmt = null;

        //checkPMReadFlag($msg_id);

        return $results;

    } catch (PDOException $e) {
        //addAlert("danger", "Oops, looks like our database encountered an error.");
        error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
        return false;
    }
}

function checkPMReadFlag($msg_id){
    try {
        global $db_table_prefix;

        $results = array();

        $db = pdoConnect();

        $sqlVars = array();

        $query = "UPDATE ".$db_table_prefix."plugin_pm
            SET receiver_read = '1'
            WHERE
            id = :msg_id";

        $stmt = $db->prepare($query);

        $sqlVars[':msg_id'] = $msg_id;

        if (!$stmt->execute($sqlVars)){
            // Error: column does not exist
            return false;
        }

        if ($stmt->rowCount() > 0)
            return true;
        else {
            //addAlert("danger", "alread set as read.");
            return false;
        }

    } catch (PDOException $e) {
        addAlert("danger", "Oops, looks like our database encountered an error.");
        error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
        return false;
    } catch (ErrorException $e) {
        addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
        return false;
    } catch (RuntimeException $e) {
        addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
        error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
        return false;
    }
}

function removePM($msg_id, $user_id, $field, $action){
    try {
        global $db_table_prefix;

        $results = array();

        $db = pdoConnect();

        $sqlVars = array();

        $query = "UPDATE ".$db_table_prefix."plugin_pm
            SET $field = '1'
            WHERE
            id = :msg_id AND $action = :user_id";

        $stmt = $db->prepare($query);

        $sqlVars[':user_id'] = $user_id;
        $sqlVars[':msg_id'] = $msg_id;

        if (!$stmt->execute($sqlVars)){
            // Error: column does not exist
            return false;
        }

        if ($stmt->rowCount() > 0)
            return true;
        else {
            addAlert("danger", "Invalid token specified.");
            return false;
        }

    } catch (PDOException $e) {
        addAlert("danger", "Oops, looks like our database encountered an error.");
        error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
        return false;
    } catch (ErrorException $e) {
        addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
        return false;
    } catch (RuntimeException $e) {
        addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
        error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
        return false;
    }
}

function fetchUserIdByUsername($user_name){
    try {
        global $db_table_prefix;

        $results = array();

        $db = pdoConnect();

        $sqlVars = array();

        $query = "select id, user_name, display_name from {$db_table_prefix}users where user_name = :user_name";

        $sqlVars[':user_name'] = $user_name;

        $stmt = $db->prepare($query);
        $stmt->execute($sqlVars);

        if (!($results = $stmt->fetch(PDO::FETCH_ASSOC))){
            addAlert("danger", "Invalid username specified");
            return false;
        }

        $stmt = null;

        return $results;

    } catch (PDOException $e) {
        addAlert("danger", "Oops, looks like our database encountered an error.");
        error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
        return false;
    } catch (ErrorException $e) {
        addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
        return false;
    }
}

function createMessage($user_id, $receiver_id, $title, $message, $isreply=NULL, $parent_id=NULL){
    try {
        global $db_table_prefix;

        $db = pdoConnect();

        $query = "INSERT INTO ".$db_table_prefix."plugin_pm (
            sender_id, receiver_id, title, message, time_sent, time_read,
            receiver_read, sender_deleted, receiver_deleted, isreply, parent_id
            )
            VALUES (
            :user_id, :receiver_id, :title, :message,
            '".time()."', '0', '0',
            '0', '0', :isreply, :parent_id
            )";

        $sqlVars = array(
            ':user_id' => $user_id,
            ':receiver_id' => $receiver_id,
            ':title' => $title,
            ':message' => $message,
            ':isreply' => $isreply,
            ':parent_id' => $parent_id
        );
        $stmt = $db->prepare($query);
        if (!$stmt->execute($sqlVars)){
            // Error: column does not exist
            return false;
        }

        $inserted_id = $db->lastInsertId();

        $stmt = null;

        return $inserted_id;

    } catch (PDOException $e) {
        addAlert("danger", "Oops, looks like our database encountered an error.");
        error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
        return false;
    } catch (ErrorException $e) {
        addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
        return false;
    }
}