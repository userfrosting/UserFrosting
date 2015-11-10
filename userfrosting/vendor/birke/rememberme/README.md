# Secure "Remember Me"
This library implements the best practices for implementing a secure
"Remember Me" functionality on web sites. Login information and unique secure 
tokens are stored in a cookie. If the user visits the site, the login information 
from the cookie is compared to information stored on the server. If the tokens 
match, the user is logged in. A user can have login cookies on several 
computers/browsers.

This library is heavily inspired by Barry Jaspan's article
"[Improved Persistent Login Cookie Best Practice][1]". The library protects
against the following attack scenarios:

 - The computer of a user is stolen or compromised, enabling the attacker to log
   in with the existing "Remember Me" cookie. The user knows this has happened.
   The user can remotely invalidate all login cookies.
 - An attacker has obtained the "Remember Me" cookie and has logged in with it.
   The user does not know this. The next time he tries to log in with the cookie
   that was stolen, he gets a warning and all login cookies are invalidated.
 - An attacker has obtained the database of login tokens from the server. The 
   stored tokens are hashed so he can't use them without computational effort
   (rainbow tables or brute force).

## Installation

	composer require birke/rememberme

## Usage example
See the `example` directory for an example.

## Improving security
The generated tokens are pseudo-random and the storage classes use the SHA1 algorithm
to hash them. If you need better security than that, overwrite the
`Authenticator::generateToken` method to generate a truly random token. If you are
using PHP >=5.5 you can use the "[password_hash][2]" and "[password_verify][3]" functions.
On lower PHP versions you could use the [userland implementations][4] of these functions.

[1]: http://jaspan.com/improved%5Fpersistent%5Flogin%5Fcookie%5Fbest%5Fpractice
[2]: http://www.php.net/manual/en/function.password-hash.php
[3]: http://www.php.net/manual/en/function.password-verify.php
[4]: https://github.com/ircmaxell/password_compat
