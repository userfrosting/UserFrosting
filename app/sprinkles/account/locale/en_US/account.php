<?php

/**
 * en_US
 *
 * US English message token translations for the 'account' sprinkle.
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n
 * @author Alexander Weissman
 */

return [
    //Common stuff shared by all Sprinkles
    "USERNAME" => "Username",
    "PASSWORD" => "Password",
    "FIRST_NAME" => "First name",
    "LAST_NAME" => "Last name",
    "EMAIL" => "Email",

    "ACCOUNT" => [
        "@TRANSLATE" => "Account",

        "ACTION_SEND_AGAIN" => "Resend verification email",

        "LOGIN" => "Login",
        "LOGIN_SOCIAL" => "Or login with",
        "LOGOUT" => "Logout",

        "REGISTER" => "Register",
        "REGISTER_ME" => "Sign me up",

        "PASSWORD_CONFIRM" => "Confirm password",
        "PASSWORD_BETWEEN" => "Password ({{min}}-{{max}} characters)",
        "PASSWORD_FORGET" => "I forgot my password",

        "SIGNIN" => "Sign in",
        "SIGNIN_OR_REGISTER" => "Sign in or register",

        "REGISTER" => "Register",

        "REMEMBER_ME" => "Remember me !",
        "REMEMBER_ME_ON_COMPUTER" => "Remember me on this computer (not recommended for public computers)",

        "CHOOSE_USERNAME" => "Choose a unique username",

        "TOS" => "terms and conditions",
        "TOS_AGREEMENT" => "By registering an account with {{site_title}}, you accept the {{&_LINK}}.",

        "PAGE" => [
            "LOGIN" => [
                "TITLE" => "Let's get started!",
                "SUBTITLE" => "Register for free, or sign in with an existing account.",
                "DESCRIPTION" => "Sign in to your {{site_name}} account, or register for a new account.",
            ]
        ],




        "ACCESS_DENIED" => "Hmm, looks like you don't have permission to do that.",
        "INACTIVE" => "Your account is in-active. Check your emails / spam folder for account activation instructions.",
        "INVALID" => "This account does not exist. It may have been deleted.  Please contact us for more information.",
        "DISABLED" => "This account has been disabled. Please contact us for more information.",
        "ACTIVATION_COMPLETE" => "You have successfully activated your account. You can now login.",
        "ALREADY_ACTIVE" => "Your account is already activated.",

        "DISPLAY_INVALID_CHARACTERS" => "Display name can only include alpha-numeric characters",
        "DISPLAYNAME_IN_USE" => "Display name '{{display_name}}' is already in use",

        "EMAIL_CHAR_LIMIT" => "Email must be between {{min}} and {{max}} characters in length.",
        "EMAIL_IN_USE" => "Email '{{email}}' is already in use",
        "EMAIL_UPDATED" => "Account email updated",

        "FORGOTPASS_INVALID_TOKEN" => "Your secret token is not valid",
        "FORGOTPASS_OLD_TOKEN" => "Token past expiration time",
        "FORGOTPASS_COULD_NOT_UPDATE" => "Couldn't update password",
        "FORGOTPASS_REQUEST_CANNED" => "Lost password request cancelled",
        "FORGOTPASS_REQUEST_EXISTS" => "There is already an outstanding lost password request on this account",
        "FORGOTPASS_REQUEST_SENT" => "A password reset link has been emailed to the address on file for user '{{user_name}}'",
        "FORGOTPASS_REQUEST_SUCCESS" => "We have emailed you instructions on how to regain access to your account",

        "INVALID_EMAIL" => "Invalid email address",
        "INVALID_USERNAME" => "Invalid username",
        "INVALID_USER_ID" => "The requested user id does not exist.",

        "LINK_ALREADY_SENT" => "An activation email has already been sent to this email address in the last {{resend_activation_threshold}} second(s). Please try again later.",
        "LOGIN_REQUIRED" => "Sorry, you must be logged in to access this resource.",
        "LOGIN_ALREADY_COMPLETE" => "You are already logged in!",

        "MASTER_NOT_EXISTS" => "You cannot register an account until the master account has been created!",

        "NEW_ACTIVATION_SENT" => "We have emailed you a new activation link, please check your email",
        "NEW_PASSWORD_LENGTH" => "New password must be between {{min}} and {{max}} characters in length",

        "PASSWORD_HASH_FAILED" => "Password hashing failed. Please contact a site administrator.",
        "PASSWORD_INVALID" => "Current password doesn't match the one we have on record",
        "PASS_CHAR_LIMIT" => "Your password must be between {{min}} and {{max}} characters in length.",
        "PASS_MISMATCH" => "Your password and confirmation password must match",
        "PASSWORD_UPDATED" => "Account password updated",
        "PASSWORD_NOTHING_TO_UPDATE" => "You cannot update with the same password",
        "PASSWORD_CONFIRM_CURRENT" => "Please confirm your current password",

        "REGISTRATION_DISABLED" => "We're sorry, account registration has been disabled.",
        "REGISTRATION_BROKEN" => "We're sorry, there is a problem with our account registration process.  Please contact us directly for assistance.",
        "REGISTRATION_LOGOUT" => "I'm sorry, you cannot register for an account while logged in. Please log out first.",
        "REGISTRATION_COMPLETE_TYPE1" => "You have successfully registered. You can now login.",
        "REGISTRATION_COMPLETE_TYPE2" => "You have successfully registered. You will soon receive an activation email. You must activate your account before logging in.",

        "SETTINGS_UPDATED" => "Account settings updated",
        "SPECIFY_USERNAME" => "Please enter your user name.",
        "SPECIFY_DISPLAY_NAME" => "Please enter your display name.",
        "SPECIFY_PASSWORD" => "Please enter your password.",
        "SPECIFY_EMAIL" => "Please enter your email address.",
        "SPECIFY_LOCALE" => "Please specify a valid locale.",
        "SPECIFY_NEW_PASSWORD" => "Please enter your new password",
        "SPECIFY_CONFIRM_PASSWORD" => "Please confirm your new password",

        "TITLE_CHAR_LIMIT" => "Titles must be between {{min}} and {{max}} characters in length.",
        "TOKEN_NOT_FOUND" => "Token does not exist / Account is already activated",

        "USER_OR_EMAIL_INVALID" => "Username or email address is invalid.",
        "USER_OR_PASS_INVALID" => "Username or password is invalid.",
        "USER_CHAR_LIMIT" => "Your username must be between {{min}} and {{max}} characters in length.",
        "USER_INVALID_CHARACTERS" => "Username can only include alpha-numeric characters",
        "USER_NO_LEAD_WS" => "Username cannot begin with whitespace",
        "USER_NO_TRAIL_WS" => "Username cannot end with whitespace",
        "USERNAME_IN_USE" => "Username '{{user_name}}' is already in use"
    ]
];
