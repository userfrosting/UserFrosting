<?php

    /********** USER MANAGEMENT INTERFACE **********/
    
    global $app;       
    
    // User creation form
    $app->get('/forms/users/?', 'UserFrosting\UserController::formUserCreate');

    // User info form (update/view)
    $app->get('/forms/users/u/:user_id/?', 'UserFrosting\UserController::formUserEdit');  

    // User edit password form
    $app->get('/forms/users/u/:user_id/password/?', 'UserFrosting\UserController::formUserEditPassword');
    
    // List users
    $app->get('/users/?', 'UserFrosting\UserController::pageUsers')->name('uri_users');    

    // List users in a particular primary group
    $app->get('/users/:primary_group/?', 'UserFrosting\UserController::pageUsers');    
        
    // User info page
    $app->get('/users/u/:user_id/?', 'UserFrosting\UserController::pageUser');
    
    // Create user
    $app->post('/users/?', 'UserFrosting\UserController::createUser');
    
    // Update user info
    $app->post('/users/u/:user_id/?', 'UserFrosting\UserController::updateUser');      
    
    // Delete user
    $app->post('/users/u/:user_id/delete/?', 'UserFrosting\UserController::deleteUser');
    