<?

/******************************************************************************************************************

Unsecured functions.  Use these only within secured functions, or when you are not directly rendering their outputs.

*******************************************************************************************************************/

//Functions that interact mainly with .users table
//------------------------------------------------------------------------------

/*****************  Basic user account status/info functions *******************/

// Determines whether or not there is a user logged in.  If there is an active session, but the user no longer exists in the database, return false.
function isUserLoggedIn() {
	global $loggedInUser, $db_table_prefix;
	if($loggedInUser == NULL){
		return false;//if $loggedInUser is null, we don't need to check the database. KISS
	}else{
        try {
            $db = pdoConnect();
            
            $sqlVars = array();        
        
            $query = "SELECT 
                id,
                password
                FROM {$db_table_prefix}users
                WHERE
                id = :user_id
                AND 
                password = :password 
                AND
                active = 1
                LIMIT 1";
            $stmt = $db->prepare($query);
            
            $sqlVars[':user_id'] = $loggedInUser->user_id;
            $sqlVars[':password'] = $loggedInUser->hash_pw;
    
            if (!$stmt->execute($sqlVars)){
                // Error: column does not exist
                return false;
            }
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row)
                return true;
            else {
                destroySession("userCakeUser");//user may have been deleted but a session lingers. delete it.
                return false;//not loggedin
            }
        } catch (PDOException $e) {
          addAlert("danger", "Oops, looks like our database encountered an error.");
          error_log($e->getMessage());
          return false;
        } catch (ErrorException $e) {
          addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
          return false;
        } catch (RuntimeException $e) {
          addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
          error_log($e->getMessage());
          return false;
        }
    }
}

//Check if a user ID exists in the DB
function userIdExists($id) {
    return userValueExists('id', $id);
}

//Checks if a username exists in the DB.  
function usernameExists($user_name) {
    return userValueExists('user_name', $user_name);
}

//Check if a display name exists in the DB.
function displayNameExists($display_name) {
    return userValueExists('display_name', $display_name);
}

//Check if an email exists in the DB
function emailExists($email) {
    return userValueExists('email', $email);
}

// Determine if a user with the specified value for a specified column exists.  Returns true if the username exists, false if not or on error.
function userValueExists($column, $data) {
    try {
        global $db_table_prefix;
        
        $results = array();
        
        $db = pdoConnect();
        
        $sqlVars = array();
        
        $query = "SELECT active
		FROM ".$db_table_prefix."users
		WHERE
		$column = :data
		LIMIT 1";
        
        $stmt = $db->prepare($query);
        
        $sqlVars[':data'] = $data;

        if (!$stmt->execute($sqlVars)){
            // Error: column does not exist
            return false;
        }
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row)
            return true;
        else
            return false;
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    } catch (RuntimeException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      error_log($e->getMessage());
      return false;
    }
}

//Check if a user name and email belong to the same user
function emailUsernameLinked($email,$user_name) {
    try {
        global $db_table_prefix;
        
        $results = array();
        
        $db = pdoConnect();
        
        $sqlVars = array();
        
        $query = "SELECT active
		FROM ".$db_table_prefix."users
		WHERE
		user_name = :user_name AND
        email = :email
		LIMIT 1";
        
        $stmt = $db->prepare($query);
        
        $sqlVars[':user_name'] = $user_name;
        $sqlVars[':email'] = $email;

        if (!$stmt->execute($sqlVars)){
            // Error: column does not exist
            return false;
        }
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row)
            return true;
        else
            return false;
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    } catch (RuntimeException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      error_log($e->getMessage());
      return false;
    }
}

/*****************  User fetch data functions *******************/

// Shortcut functions for fetchUserAuth by different parameters
function fetchUserAuthById($user_id){
    return fetchUserAuth('id', $user_id);
}

function fetchUserAuthByActivationToken($activation_token){
    return fetchUserAuth('activation_token', $activation_token);
}

function fetchUserAuthByUserName($user_name){
    return fetchUserAuth('user_name', $user_name);
}

// Similar to loadUser, except additionally loads authentication data including password hash and activation request data
function fetchUserAuth($column, $data){    
    try {
        global $db_table_prefix;
        
        $results = array();
        
        $db = pdoConnect();
        
        $sqlVars = array();
        
        $query = "SELECT 
            id,
            user_name,
            display_name,
            password,
            email,
            activation_token,
            last_activation_request,
            lost_password_request,
            active,
            title,
            sign_up_stamp,
            last_sign_in_stamp,
            enabled
            FROM ".$db_table_prefix."users
            WHERE
            $column = :data
            LIMIT 1";
            
        $stmt = $db->prepare($query);
        
        $sqlVars[':data'] = $data;
        
        $stmt->execute($sqlVars);
          
        if (!($results = $stmt->fetch(PDO::FETCH_ASSOC))){
            // The user does not exist
            return false;
        }
        
        $stmt = null;
        return $results;
      
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    } catch (RuntimeException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      error_log($e->getMessage());
      return false;
    }
}

// Fetch the appropriate menu for a user based on their primary group.  TODO: make this cacheable so it doesn't have to be processed each time a page is loaded.
// Hooks is an array of hook names mapped to their values
function fetchUserMenu($user_id, $hooks){
    // Get the user's primary group
    if (!($primary_group = fetchUserPrimaryGroup($user_id))){
        return null;
    }
    
    $group_id = $primary_group['id'];
    
    $path = MENU_TEMPLATES . "menu-" . $group_id . ".html";
    
	$contents = file_get_contents($path);
    
    //Check to see we can access the file / it has some contents
    if(!$contents || empty($contents)) {
          addAlert("danger", "The menu for this group could not be found.");
          return null;
    } else { 
        $find = array_keys($hooks);
        $replace = array_values($hooks);
        
        //Replace hooks
        $contents = str_replace($find, $replace, $contents);
        
        return $contents;
    }
}

// Fetch the primary group for the specified user
function fetchUserPrimaryGroup($user_id){
   try {
        global $db_table_prefix;
        
        $db = pdoConnect();
        
        $sqlVars = array();
        
        $query = "SELECT ".$db_table_prefix."groups.id as id, name 
            FROM ".$db_table_prefix."user_group_matches,".$db_table_prefix."groups
            WHERE user_id = :user_id and ".$db_table_prefix."user_group_matches.group_id = ".$db_table_prefix."groups.id and
            ".$db_table_prefix."user_group_matches.is_primary = 1 LIMIT 1";
        
        $stmt = $db->prepare($query);  

        $sqlVars[":user_id"] = $user_id;
        
        if (!$stmt->execute($sqlVars)){
            // Error
            return false;
        }
            
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
        if ($row)
            return $row;
        else {
            addAlert("danger", "The user does not appear to have a primary group assigned.");
            return false;
        }
        
        $stmt = null;
        
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    }
        
}

// Fetch the home page for the specified user's primary group
function fetchUserHomePage($user_id){
   try {
        global $db_table_prefix;
        
        $db = pdoConnect();
        
        $sqlVars = array();
        
        $query = "SELECT page 
            FROM ".$db_table_prefix."user_group_matches,".$db_table_prefix."groups,".$db_table_prefix."pages 
            WHERE user_id = :user_id and ".$db_table_prefix."user_group_matches.group_id = ".$db_table_prefix."groups.id
             and ".$db_table_prefix."user_group_matches.is_primary = '1' and ".$db_table_prefix."pages.id = ".$db_table_prefix."groups.home_page_id LIMIT 1";
        
        $stmt = $db->prepare($query);  

        $sqlVars[":user_id"] = $user_id;
        
        if (!$stmt->execute($sqlVars)){
            // Error
            return false;
        }
            
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
        if ($row)
            return $row['page'];
        else {
            addAlert("danger", "The user does not appear to have a primary group assigned.");
            return false;
        }
        
        $stmt = null;
        
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    }
}

/*****************  User account activation functions *******************/

// Change a user from inactive to active by providing the secret token
function setUserActive($token) {
    try {
        global $db_table_prefix;
        
        $results = array();
        
        $db = pdoConnect();
        
        $sqlVars = array();

        $query = "UPDATE ".$db_table_prefix."users
            SET active = 1
            WHERE
            activation_token = :token
            LIMIT 1";
        
        $stmt = $db->prepare($query);
        
        $sqlVars[':token'] = $token;

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
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    } catch (RuntimeException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      error_log($e->getMessage());
      return false;
    }
}

//Check if activation token exists in DB, and that account is not already activated
function validateActivationToken($token) {
    try {
        global $db_table_prefix;
        
        $results = array();
        
        $db = pdoConnect();
        
        $sqlVars = array();
        
		$query = "SELECT active
			FROM ".$db_table_prefix."users
			WHERE active = 0
			AND
			activation_token = :token
			LIMIT 1";
  
        $stmt = $db->prepare($query);
        
        $sqlVars[':token'] = $token;

        if (!$stmt->execute($sqlVars)){
            // Error: column does not exist
            return false;
        }
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row)
            return true;
        else
            return false;
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    } catch (RuntimeException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      error_log($e->getMessage());
      return false;
    }    
}

//Input new activation token, and update the time of the most recent activation request
function updateLastActivationRequest($new_activation_token,$user_name,$email) {
    try {
        global $db_table_prefix;
        
        $db = pdoConnect();
        
        $sqlVars = array();
        $query = "UPDATE ".$db_table_prefix."users
            SET activation_token = :token,
            last_activation_request = :time
            WHERE email = :email
            AND
            user_name = :user_name";
    
        $stmt = $db->prepare($query);
        
        $sqlVars['token'] = $new_activation_token;
        $sqlVars['time'] = time();
        $sqlVars['email'] = $email;
        $sqlVars['user_name'] = $user_name;
        
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
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    } catch (RuntimeException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      error_log($e->getMessage());
      return false;
    }
}

/*****************  User lost password functions *******************/

//Check if lost password token exists in DB, that user account is active and that there is an outstanding lost password request.
function validateLostPasswordToken($token) {
    try {
        global $db_table_prefix;
        
        $db = pdoConnect();
        
        $sqlVars = array();
        
		$query = "SELECT active
			FROM ".$db_table_prefix."users
			WHERE active = 1
			AND
			activation_token = :token
			AND
			lost_password_request = 1 
			LIMIT 1";

        $stmt = $db->prepare($query);
        
        $sqlVars[':token'] = $token;

        if (!$stmt->execute($sqlVars)){
            // Error: column does not exist
            return false;
        }
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row)
            return true;
        else
            return false;
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    } catch (RuntimeException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      error_log($e->getMessage());
      return false;
    }
}

//Toggle if lost password request flag on or off.  Return true on success, false on failure.
function flagLostPasswordRequest($user_name, $value) {
    try {
        global $db_table_prefix;
        
        $db = pdoConnect();
        
        $sqlVars = array();
        
		$query = "UPDATE ".$db_table_prefix."users
		SET lost_password_request = :value
		WHERE
		user_name = :user_name
		LIMIT 1";
        
        $stmt = $db->prepare($query);
        
	    $sqlVars['value'] = $value;
        $sqlVars['user_name'] = $user_name;

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
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    } catch (RuntimeException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      error_log($e->getMessage());
      return false;
    }
}

//Generate a random password, and new token
function updatePasswordFromToken($password, $current_token) {
    try {
        global $db_table_prefix;
        
        $db = pdoConnect();
        
        $sqlVars = array();

        $query = "UPDATE ".$db_table_prefix."users
            SET password = :password,
            activation_token = :new_token
            WHERE
            activation_token = :current_token";
        
		$stmt = $db->prepare($query);
        
	    $sqlVars[':password'] = $password;
        $sqlVars[':new_token'] = generateActivationToken();
        $sqlVars[':current_token'] = $current_token;
	
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
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    } catch (RuntimeException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      error_log($e->getMessage());
      return false;
    }
}

//Functions that interact mainly with .groups table
//------------------------------------------------------------------------------

//Check if a group exists in the DB
function groupIdExists($group_id) {
    try {
        global $db_table_prefix;
        
        $db = pdoConnect();
        
        $sqlVars = array();

        $query = "SELECT id
		FROM ".$db_table_prefix."groups
		WHERE
		id = :group_id
		LIMIT 1";
        
        $stmt = $db->prepare($query);
        
        $sqlVars[':group_id'] = $group_id;

        if (!$stmt->execute($sqlVars)){
            // Error
            return false;
        }
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row)
            return true;
        else
            return false;
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    }
}

//Check if a group name exists in the DB
function groupNameExists($name) {
    try {
        global $db_table_prefix;
        
        $db = pdoConnect();
        
        $sqlVars = array();

        $query = "SELECT id
		FROM ".$db_table_prefix."groups
		WHERE
		name = :name
		LIMIT 1";
        $stmt = $db->prepare($query);
        
        $sqlVars[':name'] = $name;

        if (!$stmt->execute($sqlVars)){
            // Error
            return false;
        }
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row)
            return true;
        else
            return false;
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    }
}

//Retrieve information for all user groups
function fetchAllGroups() {
    try {
        global $db_table_prefix;
        
        $results = array();
        
        $db = pdoConnect();
        
        $sqlVars = array();

        $query = "SELECT 
            id,
            name,
            is_default,
            can_delete
            FROM ".$db_table_prefix."groups"; 
        
        $stmt = $db->prepare($query);

        if (!$stmt->execute($sqlVars)){
            // Error
            return false;
        }
        
      while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $id = $r['id'];
          $results[$id] = $r;
      }
      $stmt = null;
      
      return $results;
      
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    }
}

//Retrieve information for a group by id
function fetchGroupDetails($group_id) {
    try {
        global $db_table_prefix;
        
        $db = pdoConnect();
        
        $sqlVars = array();

        $query = "SELECT 
            id,
            name,
            is_default,
            can_delete 
            FROM ".$db_table_prefix."groups
            WHERE
            id = :group_id
            LIMIT 1";
	
        $stmt = $db->prepare($query);

        $sqlVars[':group_id'] = $group_id;
        
        if (!$stmt->execute($sqlVars)){
            // Error
            return false;
        }
        
        if (!($results = $stmt->fetch(PDO::FETCH_ASSOC)))
            return false;
            
        $stmt = null;
      
        return $results;
        
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    }        
}

// Fetch group information for a specified user
function fetchUserGroups($user_id) {
    try {
        global $db_table_prefix;
        
        $results = array();
        
        $db = pdoConnect();
        
        $sqlVars = array();
        
        $query = "SELECT ".$db_table_prefix."groups.id as id, name 
            FROM ".$db_table_prefix."user_group_matches,".$db_table_prefix."groups
            WHERE user_id = :user_id and ".$db_table_prefix."user_group_matches.group_id = ".$db_table_prefix."groups.id
            ";
        
        $stmt = $db->prepare($query);    

        $sqlVars[":user_id"] = $user_id;
        
        if (!$stmt->execute($sqlVars)){
            // Error
            return false;
        }
            
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
              $id = $r['id'];
              $results[$id] = $r;
        }
        $stmt = null;
          
        return $results;
          
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    }
}

//Functions that interact mainly with .user_group_matches table
//------------------------------------------------------------------------------

// Add a user to the default groups.  TODO: check that user exists and isn't already assigned to group.
function addUserToDefaultGroups($user_id){
    try {
        global $db_table_prefix;
        
        $db = pdoConnect();

        $query = "SELECT 
            id 
            FROM ".$db_table_prefix."groups where is_default='1'"; 
        
        $stmt = $db->prepare($query);

        if (!$stmt->execute($sqlVars)){
            // Error
            return false;
        }
        
        // Query to insert group membership
        $query_user = "INSERT INTO ".$db_table_prefix."user_group_matches (
		group_id,
		user_id
		)
		VALUES (
		:group_id,
		:user_id
		)";			
        
        $stmt_user = $db->prepare($query_user);
        
        // Insert match for each default group
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $group_id = $r['id'];
            $sqlVars = array(':group_id' => $group_id, ':user_id' => $user_id);
            $stmt_user->execute($sqlVars);   
        }
        $stmt = null;
      
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    }
}

//Match group(s) with user
function addUserToGroups($group_ids, $user_id) {
    try {
        global $db_table_prefix;
        
        $results = array();
        
        $db = pdoConnect();
        
        $query = "INSERT INTO ".$db_table_prefix."user_group_matches (
		group_id,
		user_id
		)
		VALUES (
		:group_id,
		:user_id
		)";
        
        $stmt->prepare($query);
        
        if (is_array($group_ids)){
            foreach($group_ids as $id){
                $sqlVars = array(':group_id' => $id, ':user_id' => $user_id);
                $stmt->execute($sqlVars);
                $i++;
            }
        } else {
            $sqlVars = array(':group_id' => $group_ids, ':user_id' => $user_id);
            $stmt->execute($sqlVars);
            $i++;
        }
        $stmt = null;
        return $i;
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    }        
}

//Unmatch group(s) from a user
function removeUserFromGroups($group_ids, $user) {
    try {
        global $db_table_prefix;
        
        $results = array();
        
        $db = pdoConnect();
        
        $query = "DELETE FROM ".$db_table_prefix."user_group_matches 
		WHERE group_id = :group_id
		AND user_id = :user_id";
        
        $stmt->prepare($query);
        
        if (is_array($group_ids)){
            foreach($group_ids as $id){
                $sqlVars = array(':group_id' => $id, ':user_id' => $user_id);
                $stmt->execute($sqlVars);
                $i++;
            }
        } else {
            $sqlVars = array(':group_id' => $group_ids, ':user_id' => $user_id);
            $stmt->execute($sqlVars);
            $i++;
        }
        $stmt = null;
        return $i;
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    } 
}

// TODO: Match user(s) to a group
function addUsersToGroup($group_id, $user_ids){

}

// TODO: Unmatch user(s) from a group
function removeUsersFromGroup($group_id, $user_ids){

}

//Functions that interact mainly with .configuration table
//------------------------------------------------------------------------------

// Fetch the value of a configuration parameter by name
function fetchConfigParameter($name){
    try {
        global $db_table_prefix;
        
        $results = array();
        
        $db = pdoConnect();
        
        $query = "SELECT id, value
		FROM ".$db_table_prefix."configuration WHERE name = :name";	
        
        if (!$stmt->prepare($query))
            return false;

        $sqlVars[":name"] = $name;
        
        if (!$stmt->execute($sqlVars)){
            // Error
            return false;
        }
            
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
        if ($row)
            return $row['value'];
        else {
            addAlert("danger", "The specified configuration parameter could not be found.");
            return false;
        }
        
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    } 	
}

// Retrieve an array containing all site configuration parameters
function fetchConfigParameters(){
    try {
        global $db_table_prefix;
        
        $results = array();
        
        $db = pdoConnect();
        
        $query = "SELECT id, name, value
        FROM ".$db_table_prefix."configuration";
        
        $stmt = $db->prepare($query);    
        
        if (!$stmt->execute()){
            // Error
            return false;
        }
            
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
              $name = $r['name'];
              $results[$name] = $r;
        }
        $stmt = null;
          
        return $results;
          
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    }
}

//Update configuration table with array of values mapped setting name => setting value
function updateConfig($settings) {
    try {
        global $db_table_prefix;
        
        $results = array();
        
        $db = pdoConnect();
        
        $query = "UPDATE ".$db_table_prefix."configuration
            SET 
            value = :value
            WHERE
            name = :name";
        
        $stmt = $db->prepare($query);    
        
        foreach ($settings as $name => $value){
            $sqlVars = array(':name' => $name, ':value' => $value);
            $stmt->execute($sqlVars);
        }
        
        if ($stmt->rowCount() > 0)
            return true;
        else {
            return false;
        } 
        
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    }         
}

// Delete a specified configuration parameter (by name)
function deleteConfigParameter($name){
    try {
        global $db_table_prefix;
        
        $results = array();
        
        $db = pdoConnect();
        
        $query = "DELETE
		FROM ".$db_table_prefix."configuration WHERE name = :name";	
	
        if (!$stmt->prepare($query))
            return false;

        $sqlVars[":name"] = $name;
        
        if (!$stmt->execute($sqlVars)){
            // Error
            return false;
        }
        
        if ($stmt->rowCount() > 0)
            return true;
        else {
            return false;
        } 
        
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    }  
}

//Functions that interact mainly with .pages table
//------------------------------------------------------------------------------

//Add a page to the DB
function createPages($pages) {
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("INSERT INTO ".$db_table_prefix."pages (
		page
		)
		VALUES (
		?
		)");
	foreach($pages as $page){
		$stmt->bind_param("s", $page);
		$stmt->execute();
	}
	$stmt->close();
}

//Delete a page from the DB
function deletePages($pages) {
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("DELETE FROM ".$db_table_prefix."pages 
		WHERE id = ?");
	$stmt2 = $mysqli->prepare("DELETE FROM ".$db_table_prefix."permission_page_matches 
		WHERE page_id = ?");
	foreach($pages as $id){
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$stmt2->bind_param("i", $id);
		$stmt2->execute();
	}
	$stmt->close();
	$stmt2->close();
}

//Fetch information on all pages
function fetchAllPages()
{
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("SELECT 
		id,
		page,
		private
		FROM ".$db_table_prefix."pages");
	$stmt->execute();
	$stmt->bind_result($id, $page, $private);
	while ($stmt->fetch()){
		$row[$page] = array('id' => $id, 'page' => $page, 'private' => $private);
	}
	$stmt->close();
	if (isset($row)){
		return ($row);
	}
}

//Fetch information for a specific page by id
function fetchPageDetails($id)
{
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("SELECT 
		id,
		page,
		private
		FROM ".$db_table_prefix."pages
		WHERE
		id = ?
		LIMIT 1");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$stmt->bind_result($id, $page, $private);
	while ($stmt->fetch()){
		$row = array('id' => $id, 'page' => $page, 'private' => $private);
	}
	$stmt->close();
	return ($row);
}

//Fetch information for a specific page by name
function fetchPageDetailsByName($name){
    try {
        global $db_table_prefix;
        
        $results = array();
        
        $db = pdoConnect();
        
        $sqlVars = array();
        
        $query = "SELECT 
            id,
            page,
            private
            FROM ".$db_table_prefix."pages
            WHERE
            page = :name
            LIMIT 1";
        
        $stmt = $db->prepare($query);

        $sqlVars[":name"] = $name;
        
        if (!$stmt->execute($sqlVars)){
            // Error
            return false;
        }
            
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
        if ($row)
            return $row;
        else {
            addAlert("danger", "The specified page details could not be found.");
            return false;
        }    
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    }
}

//Check if a page ID exists
function pageIdExists($id)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("SELECT private
		FROM ".$db_table_prefix."pages
		WHERE
		id = ?
		LIMIT 1");
	$stmt->bind_param("i", $id);	
	$stmt->execute();
	$stmt->store_result();	
	$num_returns = $stmt->num_rows;
	$stmt->close();
	
	if ($num_returns > 0)
	{
		return true;
	}
	else
	{
		return false;	
	}
}

//Toggle private/public setting of a page
function updatePrivate($id, $private)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."pages
		SET 
		private = ?
		WHERE
		id = ?");
	$stmt->bind_param("ii", $private, $id);
	$result = $stmt->execute();
	$stmt->close();	
	return $result;	
}

//Functions that interact mainly with .permission_page_matches table
//------------------------------------------------------------------------------

//Match permission level(s) with page(s)
function addPage($page, $permission) {
	global $mysqli,$db_table_prefix; 
	$i = 0;
	$stmt = $mysqli->prepare("INSERT INTO ".$db_table_prefix."permission_page_matches (
		permission_id,
		page_id
		)
		VALUES (
		?,
		?
		)");
	if (is_array($permission)){
		foreach($permission as $id){
			$stmt->bind_param("ii", $id, $page);
			$stmt->execute();
			$i++;
		}
	}
	elseif (is_array($page)){
		foreach($page as $id){
			$stmt->bind_param("ii", $permission, $id);
			$stmt->execute();
			$i++;
		}
	}
	else {
		$stmt->bind_param("ii", $permission, $page);
		$stmt->execute();
		$i++;
	}
	$stmt->close();
	return $i;
}

// Check whether a particular user has access to a particular page
function userPageMatchExists($user_id, $page_id){
   try {
        global $db_table_prefix;
        
        $db = pdoConnect();
        
        $sqlVars = array();
        
        $query = "SELECT page_id 
            FROM ".$db_table_prefix."user_group_matches,".$db_table_prefix."group_page_matches 
            WHERE ".$db_table_prefix."user_group_matches.user_id = :user_id and ".
                    $db_table_prefix."user_group_matches.group_id = ".$db_table_prefix."group_page_matches.group_id and ".
                    $db_table_prefix."group_page_matches.page_id = :page_id LIMIT 1";
        
        $stmt = $db->prepare($query);  

        $sqlVars[":user_id"] = $user_id;
        $sqlVars[":page_id"] = $page_id;
        
        if (!$stmt->execute($sqlVars)){
            // Error
            return false;
        }
            
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
        if ($row)
            return true;
        else {
            addAlert("danger", "The specified user does not have access to this page.");
            return false;
        }
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
      return false;
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      return false;
    }
}

//Retrieve list of groups that can access a page
function fetchPagePermissions($page_id)
{
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("SELECT
		id,
		permission_id
		FROM ".$db_table_prefix."permission_page_matches
		WHERE page_id = ?
		");
	$stmt->bind_param("i", $page_id);	
	$stmt->execute();
	$stmt->bind_result($id, $permission);
	while ($stmt->fetch()){
		$row[$permission] = array('id' => $id, 'permission_id' => $permission);
	}
	$stmt->close();
	if (isset($row)){
		return ($row);
	}
}

//Retrieve list of pages that a group can access
function fetchPermissionPages($permission_id)
{
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("SELECT
		id,
		page_id
		FROM ".$db_table_prefix."permission_page_matches
		WHERE permission_id = ?
		");
	$stmt->bind_param("i", $permission_id);	
	$stmt->execute();
	$stmt->bind_result($id, $page);
	while ($stmt->fetch()){
		$row[$page] = array('id' => $id, 'permission_id' => $page);
	}
	$stmt->close();
	if (isset($row)){
		return ($row);
	}
}

//Unmatched permission and page
function removePage($page, $permission) {
	global $mysqli,$db_table_prefix; 
	$i = 0;
	$stmt = $mysqli->prepare("DELETE FROM ".$db_table_prefix."permission_page_matches 
		WHERE page_id = ?
		AND permission_id =?");
	if (is_array($page)){
		foreach($page as $id){
			$stmt->bind_param("ii", $id, $permission);
			$stmt->execute();
			$i++;
		}
	}
	elseif (is_array($permission)){
		foreach($permission as $id){
			$stmt->bind_param("ii", $page, $id);
			$stmt->execute();
			$i++;
		}
	}
	else {
		$stmt->bind_param("ii", $page, $permission);
		$stmt->execute();
		$i++;
	}
	$stmt->close();
	return $i;
}

?>