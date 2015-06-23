<?php

namespace UserFrosting;

// These define the interfaces for the database object interface.  Any other implementations you write for the model MUST implement these interfaces.

interface GroupObjectInterface {
    public function getUsers();
}

interface GroupLoaderInterface {

}

interface UserObjectInterface {
    public function isGuest();
    public function getGroups();
    public function addGroup($group_id);
    public function removeGroup($group_id);
    public function getPrimaryGroup();
    public function checkAccess($hook, $params);
    public function verifyPassword($password);
    public function login();
}

interface UserLoaderInterface {
    public static function generateActivationToken($gen = null);
}

interface ObjectLoaderInterface {
    public static function exists($value, $name = "id");
    public static function fetch($value, $name = "id");
    public static function fetchAll($value = null, $name = null);
}

interface DatabaseObjectInterface {
    public function columns();
    public function table();
    public function __isset($name);
    public function __get($name);
    public function __set($name, $value);  
    public function fresh();
    public function export();
    public function store();
}

interface DatabaseInterface {
    public static function connection();
    public static function getInfo();
}

/**
 * SiteSettingsInterface Interface
 *
 * A interface for site settings database object classes.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @link http://alexanderweissman.com
 */
interface SiteSettingsInterface {
    
    /**
     * Fetch the settings from the database.
     *
     * @return array An array of site settings, containing the name and description for each setting.
     */
    public function fetchSettings();

    public function __isset($name);
    public function __set($name, $value);
    public function __get($name);
    public function set($plugin, $name, $value = null, $description = null);
    public function register($plugin, $name, $label, $type = "text", $options = []);
    public function getRegisteredSettings();
    public function getLocales();
    public function getThemes();
    public function getSystemInfo();
    public function getLog($lines = null);
    public function store();
}
