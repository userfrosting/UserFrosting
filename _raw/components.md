## Users

UserFrosting is a system that centers around **user** accounts and user management.  It currently supports the following features:

### For all users
- User account registration
- User account login
- Account activation via email links
- Self-serve account password reset
- User account settings
- Individual user language and locale (i18n) support

### For admins
- Special `root` master account with unlimited privileges
- Control privileges for users and groups
- Sortable, searchable table of user info
- See when users last logged in
- Create and delete users
- Edit user details
- Temporarily enable/disable user accounts
- Enable/disable account registration
- Manually activate accounts
- Assign users to different groups
- Control timeout for password reset requests, activation requests

## Groups

A user can belong to one or more **groups**, which determines which resources on your site they can access.

Users can also be assigned a primary group, which is used to determine their theme and other aspects of their experience.

- Sortable, searchable table of group info
- Create and delete groups
- Edit group details
- Set theme, landing page, and icons for primary groups
- Automatically add newly registered users to default groups
- Associate authorization rules with groups

## URL Structure and REST

UserFrosting uses a (nearly) RESTful URL naming scheme.  REST means that URLs are designed to refer to specific resources, which can be retrieved (via a `GET` request) or modified (via a `POST` request).  UserFrosting does not use the `PUT` or `DELETE` HTTP methods, as these are not widely supported by browsers.

Example URLs:

```
GET  /users             // List users
GET  /users/u/1         // View info for user 1
POST /users             // Create a new user
POST /users/u/1         // Update info for user 1
POST /users/u/1/delete  // Delete user 1 (this is not RESTful, but many browsers still don't support DELETE)

GET /forms/users/u/1?mode="view"     // Get a form to view user info for user 1
GET /forms/users/u/1?mode="update"   // Get a form to update user info for user 1
GET /forms/users                     // Get a form to create a new user

```

## Sessions

UserFrosting keeps everything that it needs for a user session in the `$_SESSION["userfrosting"]` key.  This includes the following:

- `$_SESSION["userfrosting"]["user"]`: A `User` object for the currently logged-in user.  Can also be accessed via `$app->user`.
- `$_SESSION["userfrosting"]["alerts"]`: A `MessageStream` object, that stores persistent messages.  Can also be accessed via `$app->alerts`.
- `$_SESSION["userfrosting"]["captcha"]`: The most recently generated captcha code, used to verify new account registration.

## Authentication

UserFrosting 0.3.0 uses the same robust authentication system, with Blowfish password hashing.  Password resets are done via a short-term expiring token.

Any visitor to your site who is not logged in is considered a "guest user".  This means that there is no longer any need to do a separate check to see if a user is logged in - the controller can simply check if a user is authorized.

By default, the guest user is not authorized to do anything.

## Authorization Hooks and Access Control

UserFrosting controls access via **authorization hooks**, which represent a "checkpoint" in the codebase to determine whether or not a user is allowed to view or manipulate the model in some way.  Hooks are represented by a unique name.

The developer can then call the function `checkAccess` on a given hook at any place in the code where she wants to control access.  Think of them as the guards of the castle that is your website.  Hooks can be used to control access to entire pages (by calling them at the beginning of a route), or to control specific components and behaviors of your application.

For example, suppose we want to control whether or not someone is allowed to update a message on a message board.  Let's call our hook `update_message`.  Suppose we are processing a POST request that contains the updated contents of the message.  For the sake of example, we've just hardcoded the request data as `$post` (in reality, you'd probably get it from `$app->request->post()`, then do some validation, etc).

```
$post = [
    "id"        => 42,
    "title"     => "Authorization control in UserFrosting",
    "content"   => "Everything you ever wanted to know!"
];

if ($app->user->checkAccess("update_message", ["message" => $post])){
    $message = MessageBoard::fetchMessage($post["id"]);
    $message->update($post);
} else {
    $ms->addMessage("danger", "The user does not have permission to update this post!");
    $app->halt(403);
}
```

So, where exactly do we decide who is authorized on the `update_message ` hook?  In the database, of course!

We use two tables, `uf_authorize_user` and `uf_authorize_group`, which we will collectively refer to as the **access control list (ACL)**.  Note that our concept of "access control list" is far more sophisticated than the traditional meaning.  UserFrosting's ACL not only handles roles (which we call groups), making it more like RBAC, but it also allows for context-sensitive access control via a set of **conditions**.  

Thus, UF provides for extremely powerful, fine-grained access control.  Rules like "allow users in group 'Tutor' to schedule sessions for students, but only if they are assigned to that student" can be defined with a single entry in the `uf_authorize_group` table.  **As far as we know, UserFrosting is the only system that allows for fully programmatic, role- and context- based access control for users.**

The tables `uf_authorize_user` and `uf_authorize_group` will associate a user/group with hooks that they are authorized for, along with a set of conditions that must be satisfied.

| id  | group_id | hook | conditions |
| ------------- | ------------- | ------------- | ------------- |
| `1` | `1` | `update_user` | `equals(self.id,user.id)&&subset(user, ["display_name", "email"])` |
| `2` | `1` | `update_message` | `hasMessage(self.id,message.id)&&subset(message, ["id", "title", "content", "subject"])` |

When `checkAccess("update_user", $params)` is called, the authorization module performs the following steps:

1. Find an entry for the hook (e.g., `update_user`) in the access control tables that either match the currently logged-in user directly, or one of the logged-in user's groups.
2. If the entry exists, check whether the conditions are satisfied (conditions can be joined together with the logical operators `&&` and `||`.)
  - A condition is checked by passing in the contents of `$params` to the `AccessCondition` function of the same name.
  - All `AccessCondition` functions also have access to the special `self` scope, which contains the information for the currently logged-in user, and the `route` scope, which contains the parameters of the current request route.
  - In this example, `equals` is an `AccessCondition` function that returns true if the two parameters are equal, and false otherwise.  In this case, we are checking to see if the currently logged-in user's (`self`) `id` matches the `id` of the user they are trying to update.  In other words, this checks that the user is only attempting to modify their own information.
3. If the conditions are met (such that the boolean string evaluates to `true`), then access is granted.
4. If the entry does not exist, or the conditions were not met, then access is denied.
5. There can only be one entry in the access control tables per group/hook pair or user/hook pair. 

### Preset URI hooks:

- uri_home
- uri_dashboard
- uri_site_settings
- uri_slim_info
- uri_php_info
- uri_zerg
- uri_users
- uri_error_log

## Theming

Themes allow custom css and layouts for different groups and users.  Twig templates, in essence, already support this via an elegant system of template directories.

Our theming system consists of a separate folder for each theme, which contains one or more HTML template files and a theme stylesheet, `css/theme.css`.  This stylesheet is imported into the public folder via a special route.  The default theme is `default`, and other themes work by overriding this content.  UF will by default look in the `default` theme for template files if if cannot find them in the current theme.

If you want to completely change the *content* of a page for a particular group, you should make a completely new page and then set permissions appropriately.  If you just want to change the *layout and style* of a page, then you should use a theme to override an existing page.

## Page Schema

## The Message Stream

Session alerts can be retrieved on the client side through the `/alerts` route.  Messages are sent back to the client in this manner, rather than directly through the HTTP response body, because in some cases we will want to persist messages across one or more requests.  

For example, after an AJAX request, you may want to refresh the page **and then** display the alerts.  If the messages were directly part of the HTTP response from the AJAX request, they would be lost after the page refresh.

## Message IDs and Internationalization (i18n)

Internationalization is handled essentially the same way that it was in UserCake - through an array that maps message ids to messages in a particular language.  In UserFrosting, this is handled through the static class `\Fortess\MessageTranslator`.  

UserFrosting uses named placeholders with the double-handlebar notation `{{user_name}}` instead of UserCake's old `%m1%` syntax.  Translation is performed using the static `translate` function:
`MessageTranslator::translate("MESSAGE_ID", [ "placeholder1" => "value1", "placeholder2" => "value2")]);`

So if `MESSAGE_ID` is defined as "This is the message, which references {{placeholder1}} and {{placeholder2}}.", the output will be:
"This is the message, which references value1 and value2."

Messages can be automatically translated and pushed to the message stream using `MessageStream`'s `addMessageTranslated` function:
`$ms->addMessageTranslated("info", "MESSAGE_ID", [ "placeholder1" => "value1", "placeholder2" => "value2")]);`

## Input Validation

UserFrosting uses the [Fortress](https://github.com/alexweissman/fortress) project to provide a schema-based system for sanitizing and validating user data.  This schema consists of a simple JSON file, with rules for how each user-submitted field should be processed.  

The `HTTPRequestFortress` class handles backend sanitization and validation, while the `ClientSideValidator` class generates client-side validation rules compatible with the [FormValidation](http://formvalidation.io) Javascript plugin.

## Site Settings


```
class SiteSettings

$app->site = new SiteSettings();        // Loads all settings from the database on instantiation
echo $app->site->site_title;            // Print site title
$app->site->site_title = "Something Different";     // Change site title
$app->site->new_option = "Something";               // FAILS!  cannot add/remove settings in core context
$app->site->set("myPlugin", "setting1", "val");    // Create or update a setting called "setting1" in the "myPlugin" context, and set its value
$app->site->register("myPlugin", "setting1", "Ninjas?", "toggle", [0 => "off", 1 => "on"]);     // Register a setting with the Site Settings page, and the specified parameters.
$app->site->store();    // Save all settings in DB

## Plugins

We are currently in the process of developing a plugin system, to make it easy for developers to extend UserFrosting via modular components.
