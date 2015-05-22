<?php

namespace UserFrosting;

trait TableInfoUser {
    protected static $_columns = [
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
            "primary_group_id"
        ];
    
    protected static $_table = "user";
}

trait TableInfoGroup {
    protected static $_columns = [
            "name",
            "is_default",
            "can_delete",
            "theme",
            "landing_page",
            "new_user_title",
            "icon"
        ];
    
    protected static $_table = "group";
}