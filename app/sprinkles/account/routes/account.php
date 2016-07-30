<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
    
$app->group('/account', function () {
    $this->get('/captcha', 'UserFrosting\Sprinkle\Account\Controller\AccountController:imageCaptcha');
    
    $this->get('/forgot-password', 'UserFrosting\Sprinkle\Account\Controller\AccountController:pageForgotPassword');
    
    $this->get('/logout', 'UserFrosting\Sprinkle\Account\Controller\AccountController:logout');       
    
    $this->get('/resend-verification', 'UserFrosting\Sprinkle\Account\Controller\AccountController:pageResendVerification');
    
    $this->get('/reset-password', 'UserFrosting\Sprinkle\Account\Controller\AccountController:pageResetPassword');
    
    $this->get('/set-password', 'UserFrosting\Sprinkle\Account\Controller\AccountController:pageSetPassword');
    
    $this->get('/settings', 'UserFrosting\Sprinkle\Account\Controller\AccountController:pageSettings');
       
    $this->get('/sign-in-or-register', 'UserFrosting\Sprinkle\Account\Controller\AccountController:pageSignInOrRegister')->add('checkEnvironment');
    
    $this->post('/login', 'UserFrosting\Sprinkle\Account\Controller\AccountController:login');    
    
    $this->post('/register', 'UserFrosting\Sprinkle\Account\Controller\AccountController:register');
});
