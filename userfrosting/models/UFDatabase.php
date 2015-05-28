<?php

namespace UserFrosting;

// Represents the UserFrosting database.  Used for initializing connections for queries.  Set $params to the connection variables you'd like to use.
abstract class UFDatabase {

    public static $app;         // The Slim app, containing configuration info

    protected static $table_user = "user";       
    protected static $table_group = "group";
    protected static $table_group_user = "group_user";
    protected static $table_configuration = "configuration";
    protected static $table_authorize_user = "authorize_user";
    protected static $table_authorize_group = "authorize_group";    
    
    protected static $columns_user = [
            "user_name",
            "display_name",
            "password",
            "email",
            "activation_token",
            "last_activation_request",
            "lost_password_request",
            "lost_password_timestamp",
            "active",
            "title",
            "sign_up_stamp",
            "last_sign_in_stamp",
            "enabled",
            "primary_group_id",
            "locale"
        ];

    protected static $columns_group = [
            "name",
            "is_default",
            "can_delete",
            "theme",
            "landing_page",
            "new_user_title",
            "icon"
        ];
    
    public static function getTableUser(){
        return static::$app->config('db')['db_prefix'] . static::$table_user;
    }

    public static function getTableGroup(){
        return static::$app->config('db')['db_prefix'] . static::$table_group;
    }

    public static function getTableGroupUser(){
        return static::$app->config('db')['db_prefix'] . static::$table_group_user;
    }
    
    public static function getTableConfiguration(){
        return static::$app->config('db')['db_prefix'] . static::$table_configuration;
    }

    public static function getTableAuthorizeUser(){
        return static::$app->config('db')['db_prefix'] . static::$table_authorize_user;
    }

    public static function getTableAuthorizeGroup(){
        return static::$app->config('db')['db_prefix'] . static::$table_authorize_group;
    }
}
