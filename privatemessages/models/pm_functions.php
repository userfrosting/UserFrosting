<?php
/**
 * Functions for the private message system
 *
 * Tested with PHP version 5
 *
 * @author     Bryson Shepard <lilfade@fadedgaming.co>
 * @author     Project Manager: Alex Weissman
 * @copyright  2014 UserFrosting
 * @version    0.1
 * @link       http://www.userfrosting.com/
 * @link       http://www.github.com/lilfade/UF-PMSystem/
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
        receiver_deleted, parent_id
        from {$db_table_prefix}plugin_pm
        WHERE $send_rec_id = :user_id AND $deleted != '1' AND parent_id IS NULL ";

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
        receiver_deleted
        from {$db_table_prefix}plugin_pm
        WHERE id = :msg_id";

        $stmt = $db->prepare($query);

        $sqlVars[':msg_id'] = $msg_id;

        $stmt->execute($sqlVars);

        if (!($results = $stmt->fetch(PDO::FETCH_ASSOC))){
            addAlert("danger", "Invalid Message id specified");
            return false;
        }

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
        receiver_deleted, parent_id
        from {$db_table_prefix}plugin_pm
        WHERE parent_id = :msg_id";

        $stmt = $db->prepare($query);

        $sqlVars[':msg_id'] = $msg_id;

        $stmt->execute($sqlVars);
        $limit = '99999';

        $i = 0;
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $id = $i;
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

function fetchUserIdByDisplayname($user_name){
    try {
        global $db_table_prefix;

        $results = array();

        $db = pdoConnect();

        $sqlVars = array();

        $query = "select id, display_name from {$db_table_prefix}users where display_name = :user_name";

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

function createMessage($user_id, $receiver_id, $title, $message, $parent_id=NULL){
    try {
        global $db_table_prefix;

        $db = pdoConnect();

        $query = "INSERT INTO ".$db_table_prefix."plugin_pm (
          sender_id, receiver_id, title, message, time_sent,  parent_id
        )
        VALUES (
          :user_id, :receiver_id, :title, :message,
          '".time()."', :parent_id
        )";

        $sqlVars = array(
            ':user_id' => $user_id,
            ':receiver_id' => $receiver_id,
            ':title' => $title,
            ':message' => $message,
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