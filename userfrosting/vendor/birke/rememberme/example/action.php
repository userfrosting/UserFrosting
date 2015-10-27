<?php
/**
 * This file demonstrates how to use the Rememberme library.
 *
 * Some code (autoload, templating) is just simple boilerplate and no shining
 * example of how to write php applications.
 *
 * @author Gabriel Birke
 */

require_once __DIR__.'/../vendor/autoload.php';

use Birke\Rememberme;

/**
 * Helper function for redirecting and destroying the session
 * @param bool $destroySession
 * @return void
 */
function redirect($destroySession=false) {
  if($destroySession) {
    session_regenerate_id(true);
    session_destroy();
  }
  header("Location: index.php");
  exit;
}

// Normally you would store the credentials in a DB
$username = "demo";
$password = "demo";

// Initialize RememberMe Library with file storage
$storagePath = dirname(__FILE__)."/tokens";
if(!is_writable($storagePath) || !is_dir($storagePath)) {
    die("'$storagePath' does not exist or is not writable by the web server.
            To run the example, please create the directory and give it the
            correct permissions.");
}
$storage = new Rememberme\Storage\File($storagePath);
$rememberMe = new Rememberme\Authenticator($storage);

// First, we initialize the session, to see if we are already logged in
session_start();

if(!empty($_SESSION['username'])) {
  if(!empty($_GET['logout'])) {
    $rememberMe->clearCookie($_SESSION['username']);
    redirect(true);
  }

  if(!empty($_GET['completelogout'])) {
    $storage->cleanAllTriplets($_SESSION['username']);
    redirect(true);
  }

  // Check, if the Rememberme cookie exists and is still valid.
  // If not, we log out the current session
  if(!empty($_COOKIE[$rememberMe->getCookieName()]) && !$rememberMe->cookieIsValid()) {
    redirect(true);
  }

  // User is still logged in - show content
  $content = tpl("user_is_logged_in");
}
// If we are not logged in, try to log in via Rememberme cookie
else {
  // If we can present the correct tokens from the cookie, we are logged in
  $loginresult = $rememberMe->login();
  if($loginresult) {
    $_SESSION['username'] = $loginresult;
    // There is a chance that an attacker has stolen the login token, so we store
    // the fact that the user was logged in via RememberMe (instead of login form)
    $_SESSION['remembered_by_cookie'] = true;
    redirect();
  }
  else {
    // If $rememberMe returned false, check if the token was invalid
    if($rememberMe->loginTokenWasInvalid()) {
      $content = tpl("cookie_was_stolen");
    }
    // $rememberMe returned false because of invalid/missing Rememberme cookie - normal login process
    else {
      if(!empty($_POST)) {
        if($username == $_POST['username'] && $password == $_POST['password']) {
          session_regenerate_id();
          $_SESSION['username'] = $username;
          // If the user wants to be remembered, create Rememberme cookie
          if(!empty($_POST['rememberme'])) {
            $rememberMe->createCookie($username);
          }
          else {
            $rememberMe->clearCookie();
          }
          redirect();
        }
        else {
          $content = tpl("login", "Invalid credentials");
        }
      }
      else {
        $content = tpl("login");
      }
    }
  }
}

// template function for including content, nothing interesting
function tpl($template, $msg="") {
  $fn = __DIR__ . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . $template . ".php";
  if(file_exists($fn)) {
    ob_start();
    include $fn;
    return ob_get_clean();
  }
  else {
    return "Template $fn not found";
  }
}