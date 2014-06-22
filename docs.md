---
layout: default
title: "UserFrosting: Documentation"
---   
# Getting Started with UserFrosting 0.2.0 (butterflyknife)

## Overview

Welcome to UserFrosting, a secure, modern user management system for web services and applications.  UserFrosting is based on the popular UserCake system, written in PHP.  UserFrosting improves on this system by adding a sleek, intuitive frontend interface based on HTML5 and Twitter Bootstrap.  We've also separated the backend PHP machinery that interacts with the database from the frontend code base.  The frontend and backend talk to each other via AJAX and JSON.

### Why UserFrosting?

This project grew out of a need for a simple user management system for my tutoring business, [Bloomington Tutors](http://bloomingtontutors.com).  I wanted something that I could develop rapidly and easily customize for the needs of my business.  Since my [prior web development experience](http://alexanderweissman.com/completed-projects/) was in pure PHP, I decided to go with the PHP-based UserCake system.  Over time I modified and expanded the codebase, turning it into the UserFrosting project. 

### Why is the new version called "butterflyknife"?

When a caterpillar undergoes metamorphosis, it liquifies all of its internal organs inside its cocoon, rearranging the bits and pieces to build a butterfly.  This is essentially what we have done with the codebase from the the previous version, which was essentially organized the same way as UserCake.  Butterflyknife more cleanly separates code from content, and explicitly distinguishes backend (`api`) pages from the frontend (`account`) pages.  The "knife" part captures the precision control that the new authorization system offers.  Put "butterfly" and "knife" together, and you get the name of a well-known tool which is known for its rapid deployability and elegant design.

### Why not use Node/Drupal/Django/RoR/(insert favorite framework here)?

I chose PHP because PHP is what I know from my prior experience as a web developer. Additionally, PHP remains extremely popular and well-supported.  I chose not to use a framework because I wanted something that I could understand easily and develop rapidly from an existing PHP codebase.

## Components

### Users

UserFrosting is all about, well, **users**.  Specifically, it's for situations when you want users of your site to have an account on your site and be able to log in with a username (or email address) and password.  This is known as **authentication**.  You may also want your users to be able to manage their account information (such as changing their email address, password, or other information), or register a new account for themselves when they visit your site.  You may also want to control what individual users will see when they log into your site, and what types of things they can or cannot do when logged in.  This last concept is often referred to as **authorization**.

User accounts can be created in two ways: by another user who is authorized to create user accounts (such as site administrators), or directly through self-registration from the "registration" page.  This second method can be enabled or disabled by administrators through the "site settings" page.  Self-registered users can also be required to activate their account through an email link.  If this feature is enabled (through the "email activation" button in site settings), newly registered users will be emailed a link containing a unique **activation token**.  The user must then click this link before they will be able to log in.  Accounts can also be manually activated by administrators.  For more details on account registration and activation, see the "Account Registration" page.

Administrators can also **edit** user details, temporarily **disable** user accounts from the "users" page, or **delete** a user account entirely.  For more information, see the "User Management" section.

There is one special account, called the **root account**.  The root account is the first account created during the installation process (see "Installation").  It is defined as the account with `user_id` of `1`, although this can be changed in `models/config.php`.  The root account cannot be deleted, and is automatically granted permission for every action and page (see "Authorization").

### Groups

**Groups** are used to control authorization for multiple users at once, as well as customize the appearance and layout of the pages that each user sees.  Each user can belong to one or more groups, but only one group will be considered the user's **primary group**.  The primary group is used to determine which page the user will land on when they log in, as well as the formatting for their side menu bar.  Primary group assignment can also be used as a criteria for authorization: for example, you may authorize a group of site moderators to disable user accounts, but only for users whose primary group is "User".  For more details, see the "Authorization" section.

Groups can be managed from the "Groups" page under "Site Settings".  Here, one may edit group names, and set the landing page for primary members of that group.  Additionally, groups can be set as **default groups**.  Self-registered users are automatically added to the default groups when they create their account.  One default group can also be set as the **default primary group**.  Self-registered users will have their primary group set to this default group.

Groups can be authorized to perform certain actions or access certain pages.  Users automatically gain all the permissions associated with all groups to which they belong.  UserFrosting uses a default-deny authorization scheme, which means that users can only be denied access to an action or page by being omitted from groups that have that access.  For more information, see the "Authorization" section.

### Account Pages

**Account pages** make up the navigable content of your site.  Each page is implemented as a `.php` file, and can contain PHP, HTML, and Javascript.  We recommend that you separate your visible content (HTML) from the PHP as much as possible as a best practice.  This can be done by using Javascript/AJAX to mediate interaction between your account pages and a set of backend **api pages**.  For more information on this, see the section "Separation of frontend and backend".

The default installation comes with the following pages:

* `account_settings.php`: The page where users can change their email address and password.
* `dashboard.php`: A sample dashboard page for users.  Doesn't do anything currently, but could be used to display messages, a feed, or other high-importance information.
* `dashboard_admin.php`: A sample dashboard page for administrators.  Doesn't do anything currently, but could be used to display site statistics, monitor user activity, or manage other activities.
* `groups.php`: The page where administrators can manage groups.  Allows admins to create, edit, and delete groups, set default and default primary groups.
* `header.php`: Not a navigable page.  Renders the appropriate menu bars for users, usually loaded from other pages via jQuery, for example:

```
$('.navbar').load('header.php', function() {
    $('.navitem-dashboard').addClass('active');
});
```

* `includes.php`: Not a navigable page.  Loads core jQuery, bootstrap, and UserFrosting javascript modules.  Also, applies appropriate CSS styles for users.
* `index.php`: The first page that users are sent to upon login.  Automatically redirects users to their home page.
* `logout.php`: Logs the current user out.  We may move this to the `api` directory soon.
* `site_authorization.php`: The page where administrators can manage user, group, and page authorization.  See "Authorization" for more information.
* `site_settings.php`: The page where administrators can change settings for the site, including site name, enable or disable account registration, email login, and more.
* `user_details.php`: The page where administrators can view and edit details for a user, including group membership.  Takes the GET parameter `id`, where `id` is the id of the user to be displayed. 
* `users.php`: The page where administrators can view a list of all users, edit their account information, activate, disable, and delete accounts.

These are located in the `account` subdirectory.  You can manually add additional subdirectories in `models/config.php` by adding the path to the `$page_include_paths` array.

### Secure Actions

Like UserCake, the first version of UserFrosting was only able to control access at the page level.  Each group either had or didn't have access to a particular page.  When I started implementing the tutor management system for [my company](http://bloomingtontutors.com), I quickly realized that I needed to be able to allow different users access to different resources on the same page.  Furthermore, I realized that I needed a user's authorization to depend on certain contexts.  For example, I wanted to allow tutors to edit a student's details, but only if they had been assigned to that student.  Of course, this could be implemented with something like:

```
if (hasStudent($loggedInUser->user_id, $student_id)){
  updateUser($student_id, ...);
}
```

But I didn't want to hard code these rules throughout my site, and then have to manually update each one if I wanted to change permission rules.  I needed a solution that allowed contextual access control, with all of the rules kept in one place.

Enter the concept of **secure actions**.  Secure actions are functions that perform some atomic operation, such as modifying a user, assigning a user to a group, or viewing a list of users.  They are special because, when they are called, they first check to see if the user who called them is authorized to perform that action with the specified parameters.  This is achieved through PHP's reflective programming API, which allows a running to script to "know thyself."  A secure action calls the `checkActionPermissionSelf` function, which looks up the name of the function in the database to see if the current user (or their groups) is authorized to call it.  If so, the parameters supplied to the function are checked against one or more **permits**.  Permits are functions that check if supplied parameters meet certain criteria, and then return a true/false value.  For example, the permit `isLoggedInUser(user_id)` checks to see whether the supplied `user_id` matches the `user_id` of the currently logged in user.  If (and only if) the user passes all of the required permits for that action, then the secure function proceeds to execute.

For more information, see the "Authorization" section.

## Main Features

### Separation of frontend and backend

Although UserFrosting does not follow a strict Model-View-Controller (MVC) architecture, it respects the principle of clean separation between data, logic, and presentation.  The main boundary in UserFrosting's codebase is between the frontend of the website, which consists of static HTML, Javascript (using jQuery and Bootstrap), and CSS styles, and the backend, which consists of the MySQL database and the PHP code which interacts with the database and executes the logic.

The content which users see is generated by the pages located in the `account` subdirectory.  They consist largely of static HTML, as well as calls to various Javascript functions.  Javascript is located in the `js` subdirectory, and is used for fetching and submitting data to the backend, as well as creating dynamic effects for the frontend content.  To communicate with the backend, these functions use AJAX (Asynchronous Javascript and XML), as implemented by the jQuery framework.  AJAX calls are made to a set of **API** pages, which are located in the `api` subdirectory.  These pages accept `POST` requests for creating, updating, and deleting data, and `GET` requests for fetching data.  Data fetched by `GET` is returned as a JSON object, which the Javascript functions then parse and render on the frontend pages.

### Data Validation

For security and user experience, data submitted by the user must be validated.  For example, we might require that passwords be a certain minimum length, or that email addresses contain the `@` symbol.  Data is validated in two places: on the frontend, so that users can find out why their input is invalid without waiting for a page to reload, and on the backend, so that malicious users cannot bypass the frontend validation to submit potentially **destructive data** (for more information on destructive data, see the "Security" section).

On the frontend, validation is done via the `validateFormFields` function, located in `js/userfrosting.js`.  When this function is called on a form, it checks all submitted values against their validation rules.  These rules are specified by the `data-validate` attribute for the given field.  For example,

```
<input type='text' class='form-control' name='user_title' autocomplete='off' 
data-validate='{\"minLength\": 1, \"maxLength\": 100, \"label\": \"Title\" }/>
```

tells us that the field `user_title` must be at least 1 character and at most 100 characters long.  If the form values pass frontend validation, they are submitted to the appropriate backend API page.  On the backend, values are rechecked with the corresponding server-side rules, and potentially destructive data is neutralized using PHP's `htmlentities`.  If they fail validation on the backend, an error is pushed to the **alert stream** and an error code is returned to the frontend.

### Error Handling

Errors can occur at any point in the software, from the database all the way up to the frontend.  These errors can include runtime PHP errors, database errors, invalid data errors, or server errors.  Some errors, such as those raised by validating data, should be conveyed to the frontend user.  Others, however, should not be conveyed for security reasons, or should be "genericized" to explain that there was an error but without giving any details.  For example, you would not want users to know the details about a bug in your PHP code which they could potentially exploit, but you do want to let them know that something went wrong.

UserFrosting communicates information about errors (and exceptions, which are converted to errors) through the **alert stream**.  This is simply a PHP `SESSION` variable, which contains of an array of error messages.  It also contains an array of "success" messages, for when you want to let a user know that an action was completed successfully.  Backend functions can directly add messages to the alert stream through the PHP `addAlert` function, located in `models/funcs.php`.  These messages can then be accessed through the API page `user_alerts.php`, and rendered as necessary on frontend pages.  Frontend pages can also add messages to the alert stream by POSTing to `user_alerts.php`.  Once messages have been fetched from the stream, they are removed.

Other API pages return an error code, rather than the messages themselves.  This is because you may want to reload a page before displaying a message, and thus the alert stream allows messages to persist between page reloads.  For example if you create a new user, you may want to reload the list of users and then display a "user successfully created" message.

### Authentication

Users of your site **authenticate** ("log in") with a unique username and a password.  Passwords are hashed via the [bcrypt](https://en.wikipedia.org/wiki/Bcrypt) algorithm before being stored in the database.  Since hashing is a one-way function, this provides an essential security feature in the event that your database is compromised.  If an attacker managed to gain access to your database, they'll have a much harder time recovering your users' passwords from the hashes.  UserFrosting also appends a 22-character pseudo-random salt to user passwords, which makes it harder for attackers to reverse engineer passwords based on known hashes.  For more information, see the "Security" section.

UserFrosting can also be configured to allow logging in with the user's email address instead of their username.  This makes it a little easier for your users, since they don't have to remember an additional piece of information.  However email addresses are by definition public pieces of information, making them slightly less secure than a username, which could be kept secret.  This is a tradeoff you must consider based on the purpose and target audience of your site.

UserFrosting provides a password recovery mechanism in the event that a user forgets their password.  After specifying their username and email address, a user will be emailed a one-time password reset token that allows them to choose a new password.  Reset tokens expire after a certain period of time.  The default is 3 hours, but this can be changed in "site settings."

### Authorization

Once logged in, different users can be given permission to access different resources (such as pages) and perform different actions.  For example, you might want to allow a customer the ability to change their avatar image or password, but not their current balance.  Or, you might want to allow one user to send a message to another user, but only if they belong to the same group.  This is known as authorization.

The most common way that authorization is implemented is through an ACL (**access control list**).  In this scheme, every object (files, database entries, etc) in your system is stored along with a list of users and the actions they may perform on that object.  For example, an ACL entry might say that user Alice has permission to read and update other users' information.  If you wanted to limit Alice's access only to certain profiles, you'd need a separate entry in the ACL for each user she can access ("Alice can read and update Bob's profile information", "Alice can read and update Carlos' profile information", etc).  

In practice, systems of even moderate complexity end up requiring long, unwieldy ACLs.  For example, every time a new user account is added, you would have to add a new entry to every administrator's ACL in order for the new account to be managed by admins.  Furthermore, it is difficult to create context-dependent access control.  For example, you might want to allow certain users to download an mp3, but only on Mondays and Fridays.  Or, you might want to allow any user with a certain number of posts on a forum to create special announcements.  This would be difficult to implement with an ACL.

**Rule-based access control** solves this problem by saying that a particular user can access a particular resource, subject to certain rules.  This provides more flexibility by allowing any computable procedure to be used as a rule, rather than a simple lookup.  However, this also means that site managers must have programming expertise and access to the codebase.  The code must then be changed whenever a change in authorization rules is required.

UserFrosting attempts to combine the power of rule-based access with the simplicity of ACLs through its **secure function** feature.  Secure functions control user access in terms of the actions they can perform on a given resource/object.  These actions include the typical "create-read-update-delete" functions, but can also be extended to other types of actions, like disabling a user account, or linking a user to a group.  Individual users, as well as entire groups, can be granted permission to perform certain actions based on certain rules.  Rules are implemented in PHP as functions that return a boolean (yes/no) value given some parameters and global attributes of the currently logged in user.  For example, `isLoggedInUser(user_id)` checks to see whether the supplied `user_id` matches the `user_id` of the currently logged in user.  These functions are called **permit validators**, and a small set of predefined permit validators are provided in `models/authorization.php` as member functions of the `PermissionValidator` class.

Actions are also implemented as PHP functions, and can be found in `models/secure_functions.php`.  The relationships between users (or groups), secure actions, and permits are defined in the `user_action_permit` and `group_action_permit` tables.  Each row in these tables contains a `user_id` (or `group_id`), the function name for a secure action (e.g. `deleteUser`), and a string of permit function names joined by `&`.  When a secure action is called, it determines whether the user is permitted to perform that action with the specified parameters.  It does this via the `checkPermissionSelf` function, found in `models/authorization.php`.  This function checks the `*_action_permit` tables for rows that match the action name and the user's `user_id`, as well as any rows that match the `group_id` of a group to which the user belongs.  For each row found, it runs the specified permit functions, matching the parameters supplied to the secure action with the parameter names in the permit functions.  For example, there might be a row that looks like:

| id  | group_id | action | permits |
| ------------- | ------------- | ------------- | ------------- |
| 9 | 4 | updateUser | isUserPrimaryGroup(user_id,'3')&isLoggedInUserInGroup('3') |

This tells us that users in group `4` can only perform the action `updateUser` if the `user_id` of the user they wish to update has a primary group with `group_id=3`, and they are also a member of group `3`.  If multiple rows are matched, only one row needs to succeed to permit access.  Thus, joining permits via `&` within a row serves as an AND operation, while having multiple rows for the same user (or group) and action serves as an OR operation.

The root user automatically has permission for all actions in all contexts.

For your convenience, UserFrosting provides a simple interface for managing user and group-level authorization.  This can be found under "Site Settings -> Authorization" in the admin menu.  We provide shortcuts for some common permission settings ("always", "isLoggedInUser", "isUserPrimaryGroup", etc) which are automatically converted to the appropriate permit string.  UserFrosting also comes preloaded with typical permission settings for the "Admin" and "User" groups.  You can also write your own custom permit strings and add them to the database manually.

The default secure actions that come with UserFrosting are contained in `models/secure_functions.php`, and consist of the following:
* activateUser
* addUserToGroup
* createGroup
* createGroupActionPermit
* createUser
* createUserActionPermit
* deleteGroup
* deleteGroupActionPermit
* deleteUser
* deleteUserActionPermit
* loadGroup
* loadGroupActionPermits
* loadGroups
* loadPermissionValidators
* loadPresetPermitOptions
* loadSecureFunctions
* loadSitePages
* loadSiteSettings
* loadUser
* loadUserActionPermits
* loadUserGroups
* loadUsers
* loadUsersInGroup
* removeUserFromGroup
* updateGroup
* updateGroupActionPermit
* updatePageGroupLink
* updateSiteSettings
* updateUserActionPermit
* updateUserDisplayName
* updateUserEmail
* updateUserEnabled
* updateUserPassword
* updateUserPrimaryGroup
* updateUserTitle

The default permit validators are contained in `models/authorization.php`, and consist of the following:
* always()
* isLoggedInUser(user_id)
* isLoggedInUserInGroup(group_id)
* isUserPrimaryGroup(user_id, group_id)
* isSameGroup(group_id, group_id_2)
* isDefaultGroup(group_id)
* isActive(user_id)

You may of course add additional secure actions and permit validators - and will probably need to if you want to control access to the other features you create for your site.

### User Management



### Templating

## Design Principles

### No assembly required

If you're reading this, chances are that you already have a web project in mind.  You may have even started to code and write content for your project.  But whether it's a private forum for your WoW guild, an employee management system for your small business, or the next big social network, we're guessing that you probably want to get started on implementing your awesome new idea as quickly as possible. 

You're here because you need a way to manage user accounts for your service/forum/orbital laser cannon, and you don't want to reinvent the wheel.  There are, of course, frameworks that can do user management, but you don't want to spend your valuable time learning the ins and outs of a particular framework, only to discover that it's not really what you need.  You might also have some existing code and content that you don't want to have to rewrite to fit the framework's way of doing things.  UserFrosting is meant for developers who want to take advantage of a pre-designed user management system, but write their own code in native PHP.

Whatever the case may be, you probably want something that is ready to go straight out of the box, without forcing you to start from `Hello World`.  UserFrosting delivers this and more.  With some basic configuration and an easy-to-use installer, you'll be ready to create and manage user accounts in 5 minutes or less - all you need is a web server running PHP 5.3 or greater, and a SQL database.

### Ease of use
* Intuitive interface
* Mobile ready


### Security
UserFrosting is designed to address the most common security issues with websites that handle sensitive user data:

#### SSL/HTTPS compatibility
Unsecured ("http") websites exchange data between the user and the server in plain text.  If the connection between the user and server is not secure, this data can be intercepted, and possibly even altered and/or rerouted.  And, even if the sensitive data itself is encrypted, the user's session on the website can be stolen and impersonated unless ALL communication between the user and server is handled over SSL ("https" websites).  If you walk into any coffee shop with an unsecured wireless network, and launch a simple program such as [Firesheep](http://codebutler.com/firesheep/), you will see how huge of a problem this is, and why [Google and other companies are pushing for _everyone_ to use SSL](http://www.wired.com/2014/04/https/).

This is also why there are strict standards about websites that handle sensitive user data such as credit card numbers!  We strongly encourage anyone planning to deploy a website that handles user passwords and sessions (such as ones based on UserFrosting) to purchase an SSL certificate and deploy it on their web server.  [Namecheap](https://www.namecheap.com/support/knowledgebase/article.aspx/794/67/how-to-activate-ssl-certificate) offers basic, inexpensive certs for $9/year (you do not need to have Namecheap hosting or domain registration to use their certificates on your site).  If your web hosting happens to use cPanel, this is easy to [set up yourself](http://docs.cpanel.net/twiki/bin/view/AllDocumentation/WHMDocs/InstallCert) without needing to contact your hosting provider.  Please note that SSL on shared hosting accounts may create false security warnings for end-users with [older browsers](https://en.wikipedia.org/wiki/Server_Name_Indication#No_support).

For __local testing purposes only__ you may create a self-signed certificate.  For instructions on how to do this for XAMPP/Apache in OSX, see [this blog post](http://shahpunyerblog.blogspot.com/2007/10/create-self-signed-ssl-certificate-in.html).

#### Strong password hashing
UserFrosting uses the `password_hash` and `password_verify` functions to hash and validate passwords (new in PHP v5.5.0).  `password_hash` uses the [bcrypt](https://en.wikipedia.org/wiki/Bcrypt) algorithm, based on the Blowfish cipher.  This is stronger than SHA1 (used by UserCake), which has been demonstrated vulnerable to attack.  UserFrosting also appends a 22-character salt to user passwords, protecting against dictionary attacks.

UserFrosting provides backwards compatibility for existing UserCake user databases that have passwords hashed with MD5.  User accounts that have been hashed with MD5 will automatically be updated to the new encryption standard when the user successfully logs in.

#### Protection against cross-site request forgery (CSRF)
CSRF is an attack that relies on a user unwittingly submitting malicious data from another source while logged in to their account.  The malicious data can be embedded in an image, link, or other javascript content, on another website or in an email.  Because the user has a valid session with a website, the external content is accepted and processed.  Thus, attackers can easily change passwords or delete a user's account with this attack.

To guard against this, UserFrosting provides the `csrf_token` function (courtesy of @r3wt).  By generating a new, random CSRF token for users when they log in, inserting it into legitimate forms as a hidden field, and then having the backend form processing links check for this token before taking any action, CSRF attacks can be thwarted.

#### Protection against cross-site scripting (XSS)
XSS is another variety of attack that tricks a user, but instead of tricking the user into submitting malicious data (CSRF), it tricks the user into running malicious scripts.  This vulnerability usually appears when you allow arbitrary content (including javascript and HTML tags) to be processed and then regurgitated back to other users.  Thus, an attacker on a forum could create a new "post" that contains javascript commands.  When anyone else on the site goes to view that post, the javascript commands are executed.  Those commands could easily be instructions to transmit the user's session data to a remote server, where attackers can use it to impersonate the user.

UserFrosting guards against this by sanitizing user input before storing or otherwise acting upon it.  Please let us know if you find a place where input is not properly sanitized.

#### Protection against SQL injection
Whereas XSS tricks the _user_ into executing malicious code, SQL injection tricks the _server_ into executing malicious code; in this case, SQL statements.  Thus, sites vulnerable to SQL injection can end up executing code that, for example, deletes a table or database.

UserFrosting protects against this by using parameterized queries, which do not allow user-supplied data to be executed as code.  However there are always exceptions, and we would be glad to have some contributors test and/or help patch any possible remaining SQL injection vulnerabilities.

### Flexibility
### Extendability