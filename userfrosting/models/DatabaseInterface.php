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
    public function login($password);
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
}
