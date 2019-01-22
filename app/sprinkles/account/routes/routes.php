<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

use UserFrosting\Sprinkle\Core\Util\NoCache;

$app->group('/account', function () {
    $this->get('/captcha', 'UserFrosting\Sprinkle\Account\Controller\AccountController:imageCaptcha');

    $this->get('/check-username', 'UserFrosting\Sprinkle\Account\Controller\AccountController:checkUsername');

    $this->get('/forgot-password', 'UserFrosting\Sprinkle\Account\Controller\AccountController:pageForgotPassword')
        ->setName('forgot-password');

    $this->get('/logout', 'UserFrosting\Sprinkle\Account\Controller\AccountController:logout')
        ->add('authGuard');

    $this->get('/resend-verification', 'UserFrosting\Sprinkle\Account\Controller\AccountController:pageResendVerification');

    $this->get('/set-password/confirm', 'UserFrosting\Sprinkle\Account\Controller\AccountController:pageResetPassword');

    $this->get('/set-password/deny', 'UserFrosting\Sprinkle\Account\Controller\AccountController:denyResetPassword');

    $this->get('/register', 'UserFrosting\Sprinkle\Account\Controller\AccountController:pageRegister')
        ->add('checkEnvironment')
        ->setName('register');

    $this->get('/settings', 'UserFrosting\Sprinkle\Account\Controller\AccountController:pageSettings')
        ->add('authGuard');

    $this->get('/sign-in', 'UserFrosting\Sprinkle\Account\Controller\AccountController:pageSignIn')
        ->add('checkEnvironment')
        ->setName('login');

    $this->get('/suggest-username', 'UserFrosting\Sprinkle\Account\Controller\AccountController:suggestUsername');

    $this->get('/verify', 'UserFrosting\Sprinkle\Account\Controller\AccountController:verify');

    $this->post('/forgot-password', 'UserFrosting\Sprinkle\Account\Controller\AccountController:forgotPassword');

    $this->post('/login', 'UserFrosting\Sprinkle\Account\Controller\AccountController:login');

    $this->post('/register', 'UserFrosting\Sprinkle\Account\Controller\AccountController:register');

    $this->post('/resend-verification', 'UserFrosting\Sprinkle\Account\Controller\AccountController:resendVerification');

    $this->post('/set-password', 'UserFrosting\Sprinkle\Account\Controller\AccountController:setPassword');

    $this->post('/settings', 'UserFrosting\Sprinkle\Account\Controller\AccountController:settings')
        ->add('authGuard')
        ->setName('settings');

    $this->post('/settings/profile', 'UserFrosting\Sprinkle\Account\Controller\AccountController:profile')
        ->add('authGuard');
})->add(new NoCache());

$app->get('/modals/account/tos', 'UserFrosting\Sprinkle\Account\Controller\AccountController:getModalAccountTos');
