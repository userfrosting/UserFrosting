<?php

namespace UserFrosting;

// These define the interfaces for the database object interface.  Any other implementations you write for the model MUST implement these interfaces.

interface GroupObjectInterface {
    public function getUsers();
}

interface UserObjectInterface {
    public function getPrimaryGroup();
    public function verifyPassword($password);
    public function login($password);
}

interface UserLoaderInterface {
    public static function fetch($value, $name = "id");
}

interface DatabaseObjectInterface {
    public function columns();
    public function table();
    public function fresh();
    public function export();
    public function store();
}

interface DatabaseInterface {
    public static function connection();
}
