---
layout: default
title: "UserFrosting: Security Features"
---

# Security Features

UserFrosting is designed to address the most common security issues with websites that handle sensitive user data:

## SSL/HTTPS compatibility
Unsecured ("http") websites exchange data between the user and the server in plain text.  If the connection between the user and server is not secure, this data can be intercepted, and possibly even altered and/or rerouted.  And, even if the sensitive data itself is encrypted, the user's session on the website can be stolen and impersonated unless ALL communication between the user and server is handled over SSL ("https" websites).  If you walk into any coffee shop with an unsecured wireless network, and launch a simple program such as [Firesheep](http://codebutler.com/firesheep/), you will see how huge of a problem this is, and why [Google and other companies are pushing for _everyone_ to use SSL](http://www.wired.com/2014/04/https/).

This is also why there are strict standards about websites that handle sensitive user data such as credit card numbers!  We strongly encourage anyone planning to deploy a website that handles user passwords and sessions (such as ones based on UserFrosting) to purchase an SSL certificate and deploy it on their web server.  [Namecheap](https://www.namecheap.com/support/knowledgebase/article.aspx/794/67/how-to-activate-ssl-certificate) offers basic, inexpensive certs for $9/year (you do not need to have Namecheap hosting or domain registration to use their certificates on your site).  If your web hosting happens to use cPanel, this is easy to [set up yourself](http://docs.cpanel.net/twiki/bin/view/AllDocumentation/WHMDocs/InstallCert) without needing to contact your hosting provider.  Please note that SSL on shared hosting accounts may create false security warnings for end-users with [older browsers](https://en.wikipedia.org/wiki/Server_Name_Indication#No_support).

For __local testing purposes only__ you may create a self-signed certificate.  For instructions on how to do this for XAMPP/Apache in OSX, see [this blog post](http://shahpunyerblog.blogspot.com/2007/10/create-self-signed-ssl-certificate-in.html).

## Strong password hashing
UserFrosting uses the `password_hash` and `password_verify` functions to hash and validate passwords (new in PHP v5.5.0).  `password_hash` uses the [bcrypt](https://en.wikipedia.org/wiki/Bcrypt) algorithm, based on the Blowfish cipher.  This is stronger than SHA1 (used by UserCake), which has been demonstrated vulnerable to attack.  UserFrosting also appends a 22-character salt to user passwords, protecting against dictionary attacks.

UserFrosting provides backwards compatibility for existing UserCake user databases that have passwords hashed with MD5.  User accounts that have been hashed with MD5 will automatically be updated to the new encryption standard when the user successfully logs in.

## Protection against cross-site request forgery (CSRF)
CSRF is an attack that relies on a user unwittingly submitting malicious data from another source while logged in to their account.  The malicious data can be embedded in an image, link, or other javascript content, on another website or in an email.  Because the user has a valid session with a website, the external content is accepted and processed.  Thus, attackers can easily change passwords or delete a user's account with this attack.

To guard against this, UserFrosting provides the `csrf_token` function (courtesy of @r3wt).  By generating a new, random CSRF token for users when they log in, inserting it into legitimate forms as a hidden field, and then having the backend form processing links check for this token before taking any action, CSRF attacks can be thwarted.

## Protection against cross-site scripting (XSS)
XSS is another variety of attack that tricks a user, but instead of tricking the user into submitting malicious data (CSRF), it tricks the user into running malicious scripts.  This vulnerability usually appears when you allow arbitrary content (including javascript and HTML tags) to be processed and then regurgitated back to other users.  Thus, an attacker on a forum could create a new "post" that contains javascript commands.  When anyone else on the site goes to view that post, the javascript commands are executed.  Those commands could easily be instructions to transmit the user's session data to a remote server, where attackers can use it to impersonate the user.

UserFrosting guards against this by sanitizing user input before storing or otherwise acting upon it.  Please let us know if you find a place where input is not properly sanitized.

## Protection against SQL injection
Whereas XSS tricks the _user_ into executing malicious code, SQL injection tricks the _server_ into executing malicious code; in this case, SQL statements.  Thus, sites vulnerable to SQL injection can end up executing code that, for example, deletes a table or database.

UserFrosting protects against this by using parameterized queries, which do not allow user-supplied data to be executed as code.  However there are always exceptions, and we would be glad to have some contributors test and/or help patch any possible remaining SQL injection vulnerabilities.