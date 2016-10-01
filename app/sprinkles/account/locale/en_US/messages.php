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
    "ACCOUNT" => [
        "@TRANSLATION" => "Account",

        "ACCESS_DENIED" => "Hmm, looks like you don't have permission to do that.",

        "DISABLED" => "This account has been disabled. Please contact us for more information.",

        "EMAIL_UPDATED" => "Account email updated",

        "INVALID" => "This account does not exist. It may have been deleted.  Please contact us for more information.",

        "MASTER_NOT_EXISTS" => "You cannot register an account until the master account has been created!",

        "SESSION_COMPROMISED" => "Your session has been compromised.  You should log out on all devices, then log back in and make sure that your data has not been tampered with.",
        "SESSION_EXPIRED" => "Your session has expired.  Please sign in again.",

        "SETTINGS_UPDATED" => "Account settings updated",

        "UNVERIFIED" => "Your account is in-active. Check your emails / spam folder for account activation instructions.",

        "VERIFICATION" => [
            "ALREADY_COMPLETE" => "Your account is already verified.",
            "LINK_ALREADY_SENT" => "A verification email has already been sent to this email address in the last {{resend_activation_threshold}} second(s). Please try again later.",
            "NEW_LINK_SENT" => "We have emailed you a new verification link, please check your email",
            "RESEND" => "Resend verification email",
            "COMPLETE" => "You have successfully verified your account. You can now login.",            
            "TOKEN_NOT_FOUND" => "Verification token does not exist / Account is already verified",
        ]
    ],
    
    "EMAIL_IN_USE" => "Email '{{email}}' is already in use",

    "FIRST_NAME" => "First name",
    
    "HEADER_MESSAGE_ROOT" => "YOU ARE SIGNED IN AS THE ROOT USER",

    "LAST_NAME" => "Last name",
    
    "LOGIN" => [
        "@TRANSLATION" => "Login",

        "ALREADY_COMPLETE" => "You are already logged in!",
        "SOCIAL" => "Or login with",
        "REQUIRED" => "Sorry, you must be logged in to access this resource."
    ],
    
    "LOGOUT" => "Logout",
    
    "NAME" => "Name",
    
    "PAGE" => [
        "LOGIN" => [
            "DESCRIPTION" => "Sign in to your {{site_name}} account, or register for a new account.",
            "SUBTITLE" => "Register for free, or sign in with an existing account.",
            "TITLE" => "Let's get started!",
        ]
    ],

    "PASSWORD" => [
        "@TRANSLATION" => "Password",

        "BETWEEN" => "Password ({{min}}-{{max}} characters)",
        "CONFIRM" => "Confirm password",
        "CONFIRM_CURRENT" => "Please confirm your current password",
        "FORGET" => [
            "@TRANSLATION" => "I forgot my password",

            "COULD_NOT_UPDATE" => "Couldn't update password",
            "INVALID_TOKEN" => "Your secret token is not valid",
            "OLD_TOKEN" => "Token past expiration time",
            "REQUEST_CANNED" => "Lost password request cancelled",
            "REQUEST_EXISTS" => "There is already an outstanding lost password request on this account",
            "REQUEST_SENT" => "A password reset link has been emailed to the address on file for user '{{user_name}}'",
            "REQUEST_SUCCESS" => "We have emailed you instructions on how to regain access to your account"
        ],        
        "HASH_FAILED" => "Password hashing failed. Please contact a site administrator.",
        "INVALID" => "Current password doesn't match the one we have on record",
        "NOTHING_TO_UPDATE" => "You cannot update with the same password",
        "UPDATED" => "Account password updated"
    ],
    
    "REGISTER" => "Register",
    "REGISTER_ME" => "Sign me up",

    "REGISTRATION" => [
        "BROKEN" => "We're sorry, there is a problem with our account registration process.  Please contact us directly for assistance.",
        "COMPLETE_TYPE1" => "You have successfully registered. You can now login.",
        "COMPLETE_TYPE2" => "You have successfully registered. You will soon receive an activation email. You must activate your account before logging in.",
        "DISABLED" => "We're sorry, account registration has been disabled.",
        "LOGOUT" => "I'm sorry, you cannot register for an account while logged in. Please log out first.",   
        "WELCOME" => "Registration is fast and simple."
    ],
    
    "REMEMBER_ME" => "Remember me!",
    "REMEMBER_ME_ON_COMPUTER" => "Remember me on this computer (not recommended for public computers)",    

    "SIGNIN" => "Sign in",
    "SIGNIN_OR_REGISTER" => "Sign in or register",
    "SIGNUP" => "Sign Up",
    
    "TOS" => "terms and conditions",
    "TOS_AGREEMENT" => "By registering an account with {{site_title}}, you accept the {{&_LINK}}.",

    "USERNAME" => [
        "@TRANSLATION" => "Username",

        "CHOOSE" => "Choose a unique username",    
        "INVALID" => "Invalid username",
        "IN_USE" => "Username '{{user_name}}' is already in use."
    ],

    "USER_ID_INVALID" => "The requested user id does not exist.",
    "USER_OR_EMAIL_INVALID" => "Username or email address is invalid.",
    "USER_OR_PASS_INVALID" => "Username or password is invalid.",
    
    "WELCOME" => "Welcome back, {{first_name}}"
];
