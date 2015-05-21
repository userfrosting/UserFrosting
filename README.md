
# UserFrosting

## Goals

### For developers:

- lightweight and zero-config, works out of the box
- modular, builds on existing, widely used components
- up-to-date, using modern programming patterns including MVC
- flexible, and easy for novice developers to adapt to their needs
- clean, consistent, and well-documented code

### For users:

- secure
- easily configured from an admin interface
- attractive interface
- full-featured

## Libraries

- URL Routing and micro-framework: [Slim](http://www.slimframework.com/)
- Templating: [Twig](http://twig.sensiolabs.org/)

## Features

### Configuration



### Sessions

UserFrosting will use native PHP sessions.  We could use Slim's [encrypted session cookies](http://docs.slimframework.com/#Cookie-Session-Store), but unfortunately they only allow a max of 4KB of data - too little for what a typical use case will require.

UF will keep everything that it needs in the `$_SESSION["userfrosting"]` key.  This includes the following:

- `$_SESSION["userfrosting"]["user"]`: A `User` object for the currently logged-in user.
- `$_SESSION["userfrosting"]["alerts"]`: A `MessageStream` object, that stores persistent messages.
- `$_SESSION["userfrosting"]["captcha"]`: The most recently generated captcha code, used to verify new account registration.

The old version of UF suffers from PHP's native sessions randomly expiring.  This may be an issue related to server configuration, rather than a problem with UF itself.  More research is needed.
http://board.phpbuilder.com/showthread.php?10313632-Sessions-randomly-dropped!
https://stackoverflow.com/questions/1327351/session-should-never-expire-by-itself
http://jaspan.com/improved_persistent_login_cookie_best_practice

It could also be due to issues with other PHP applications running on the same server: https://stackoverflow.com/questions/3476538/php-sessions-timing-out-too-quickly

### Authentication

UserFrosting 0.3.0 will use the same robust authentication system, with Blowfish password hashing.  Password resets will be done via a short-term expiring token.

We will model a "guest user", which basically means any user who is not logged in.  This means that we will no longer need to do a separate check to see if a user is logged in - the controller can simply check if a user is authorized, and by default, the guest user is not authorized to do anything.

### Authorization Hooks

UserFrosting will control access via **authorization hooks**, which represent a "checkpoint" in the codebase to determine whether or not a user is allowed to view or manipulate the model in some way.  Hooks are represented by a unique name.

The developer can then call the function `checkAccess` on a given hook at any place in the code where she wants to control access.  Think of them as the guards of the castle that is your website.  Hooks can be used to control access to entire pages (by calling them at the beginning of a route), or to control specific components and behaviors of your application.

For example, suppose we want to control whether or not someone is allowed to update a message on a message board.  Let's call our hook `updateMessage`.  Suppose we are processing a POST request that contains the updated contents of the message.  For the sake of example, we've just hardcoded the request data as `$post` (in reality, you'd probably get it from `$app->request->post()`, then do some sanitization, validation, etc).

```
$post = [
    "id"        => 42,
    "title"     => "Authorization control in UserFrosting",
    "content"   => "Everything you ever wanted to know!"
];

if ($app->user->checkAccess("updateMessage", $post)){
    $message = MessageBoard::fetchMessage($post["id"]);
    $message->update($post);
} else {
    $ms->addMessage("danger", "The user does not have permission to update this post!");
    $app->halt(403);
}
```

So, where exactly do we decide who is authorized on the `updateMessage` hook?  In the database, of course!

We use two tables, `uf_authorize_user` and `uf_authorize_group`, which we will collectively refer to as the **access control list (ACL)**.  Note that our concept of "access control list" is far more sophisticated than the traditional meaning.  UserFrosting's ACL not only handles roles (which we call groups), making it more like RBAC, but it also allows for context-sensitive access control via a set of **conditions**.  Thus, UF provides for extremely powerful, fine-grained access control.  Rules like "allow users in group 'Tutor' to schedule sessions for students, but only if they are assigned to that student" can be defined with a single entry in the `uf_authorize_group` table.  **As far as we know, this is the only system that allows for fully programmatic, role- and context- based access control for users.**

The tables `uf_authorize_user` and `uf_authorize_group` will associate a user/group with hooks that they are authorized for, along with a set of conditions that must be satisfied.

| id  | group_id | hook | conditions |
| ------------- | ------------- | ------------- | ------------- |
| 1 | 1 | updateUser | equals(self.id,user.id)&&subset(user, ["display_name", "email"]) |
| 2 | 1 | updateMessage | hasMessage(self.id,message.id)&&subset(message, ["id", "title", "content", "subject"]) |

When `checkAccess("updateUser", $params)` is called, the authorization module performs the following steps:

1. Find an entry for the hook (e.g., `updateUser`) in the access control tables that either match the currently logged-in user directly, or one of the logged-in user's groups.
2. If the entry exists, check whether the conditions are satisfied (conditions can be joined together with the logical operators && and ||.)
  - A condition is checked by passing in the contents of `$params` to the `AccessCondition` function of the same name.
  - All `AccessCondition` functions also have access to the special `self` scope, which contains the information for the currently logged-in user, and the `route` scope, which contains the parameters of the current request route.
  - In this example, `equals` is an `AccessCondition` function that returns true if the two parameters are equal, and false otherwise.  In this case, we are checking to see if the currently logged-in user's (`self`) `id` matches the `id` of the user they are trying to update.  In other words, this checks that the user is only attempting to modify their own information.
3. If the conditions are met (such that the boolean string evaluates to `true`), then access is granted.
4. If the entry does not exist, or the conditions were not met, then access is denied.
5. There can only be one entry in the access control tables per group/hook pair or user/hook pair. 
 
### Data Sanitization and Validation

UserFrosting uses the [Fortress](https://github.com/alexweissman/fortress) project to provide a schema-based system for sanitizing and validating user data.  This schema consists of a simple JSON file, with rules for how each user-submitted field should be processed.  The `HTTPRequestFortress` class handles backend sanitization and validation, while the `ClientSideValidator` class generates client-side validation rules compatible with the [FormValidation](http://formvalidation.io) Javascript plugin.

Sanitization should probably happen when data is used (i.e. displayed), rather than when input.  See http://lukeplant.me.uk/blog/posts/why-escape-on-input-is-a-bad-idea/.
So, it should go something like:
raw input -> validation -> database -> sanitization -> output

 
We need a better interface for modifying permissions:
https://github.com/alexweissman/UserFrosting/issues/127
 
### Theming

We need a theming system.  See https://github.com/alexweissman/UserFrosting/issues/132.

Themes allow custom css and layouts for different groups and users.  Twig templates, in essence, already support this via an elegant system of template directories.

Our theming system consists of a separate folder for each theme, which contains one or more HTML template files and a theme stylesheet, `css/theme.css`.  This stylesheet is imported into the public folder via a special route.  The default theme is "default", and other themes work by overriding this content.  UF will by default look in the "default" theme for template files if if cannot find them in the current theme.

Menus should be automatically built based on a users' permissions.  So, a menu item should show up if and only if a user has permission to access that item.

If you want to completely change the *content* of a page for a particular group, you should make a completely new page and then set permissions appropriately.  If you just want to change the *layout and style* of a page, then you should use a theme on top of an existing page.

### Plugins

We need a plugin system that is easily extendable, and exposes the Slim `$app` instance to the plugin developer.  It should also allow the developer to modify the user's environment.

### Alerts

UserFrosting pushes all alerts (warnings, errors, success messages) to a session `MessageStream` object.  This object can be accessed by calling `getAndClearMessages()` on the `alerts` member of the Slim app.  Thus, a typical way to fetch alerts on the server side would be:

```
$alerts = $app->alerts->getAndClearMessages();
```

Session alerts can be retrieved on the client side through the /alerts route.  Messages are sent back to the client in this manner, rather than directly through the HTTP response body, because in some cases we will want to persist messages across one or more requests.  For example, after an AJAX request, you may want to refresh the page **and then** display the alerts.  If the messages were directly part of the HTTP response from the AJAX request, they would be lost after the page refresh.

### Internationalization (i18n)

Internationalization will be handled essentially the same way that it was in UserCake - through an array that maps message ids to messages in a particular language.  In UserFrosting, this is handled through the static class `\Fortess\MessageTranslator`.  Also, UserFrosting will use named placeholders with the double-handlebar notation `{{user_name}}` instead of UserCake's old `%m1%` syntax.  Translation is performed using the static `translate` function:
`MessageTranslator::translate("MESSAGE_ID", [ "placeholder1" => "value1", "placeholder2" => "value2")]);`

So if `MESSAGE_ID` is defined as "This is the message, which references {{placeholder1}} and {{placeholder2}}.", the output will be:
"This is the message, which references value1 and value2."

Messages can be automatically translated and pushed to the message stream using `MessageStream`'s `addMessageTranslated` function:
`$ms->addMessageTranslated("info", "MESSAGE_ID", [ "placeholder1" => "value1", "placeholder2" => "value2")]);`

