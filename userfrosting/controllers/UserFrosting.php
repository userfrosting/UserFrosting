<?php

namespace UserFrosting;

/**
 * The UserFrosting application class, which extends the basic Slim application.  
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @link http://www.userfrosting.com/
 * @property \Fortress\MessageTranslator $translator
 * @property \Fortress\MessageStream $alerts
 * @property \UserFrosting\SiteSettingsInterface $site
 * @property \UserFrosting\UserObjectInterface $user
 * @property \UserFrosting\PageSchema $schema
 */
class UserFrosting extends \Slim\Slim {

    /**
     * Sets up the current user session, either as a logged in user or a guest user.
     */
    public function setupUser(){
        $db_error = false;
        
        // Set user, if one is logged in
        if(isset($_SESSION["userfrosting"]["user"]) && is_object($_SESSION["userfrosting"]["user"])) {       
            // Test database connection
            try {
                // Refresh the user.  If they don't exist any more, then an exception will be thrown.
                $_SESSION["userfrosting"]["user"] = $_SESSION["userfrosting"]["user"]->fresh();
                $this->user = $_SESSION["userfrosting"]["user"];
            } catch (\PDOException $e) {
                error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
                error_log($e->getTraceAsString());
                $this->user = new User([], $this->config('user_id_guest'));
                $db_error = true;
            }
        // Otherwise, create a dummy "guest" user
        } else {
            $this->user = new User([], $this->config('user_id_guest'));
        }
        
        return $db_error;
    }

}
