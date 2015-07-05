## Creating a New Page

One of the simplest tasks in extending UserFrosting is to create a new page.  If you're a beginner, you're probably used to creating a single `.php` file which contains the content of your page (e.g. `barracks.php`), that you then view by navigating to `http://mysite.com/barracks.php`.

UserFrosting on the other hand uses the **front controller pattern**, which gives you more flexibility and decouples the URLs for your pages from the actual code that generates their content.  This means that the code for generating a page is no longer contained to a single file.  For more information about how this works, please see [Front Controllers and the Slim Microframework]({{site.url}}/navigating/#slim).

For this tutorial, we will create a simple page that lists all of the **groups** to which the current user belongs.

Let's start by creating the main route in `public/index.php`.  You will see many blocks of code that look like this:

```
$app->get('/dashboard/?', function () use ($app) {    
   // Access-controlled page
   if (!$app->user->checkAccess('uri_dashboard')){
       $app->notFound();
   }
   
   $app->render('dashboard.html', [
       'page' => [
           'author' =>         $app->site->author,
           'title' =>          "Dashboard",
           'description' =>    "Your user dashboard.",
           'alerts' =>         $app->alerts->getAndClearMessages()
       ]
   ]);          
});
```

These are **routes**, which [Slim](www.slimframework.com) uses to decide what to do when a visitor to your site goes to a particular URL.  This route handles the `/dashboard` URL.

Let's create a route for our new page, right below this block of code.  Let's call the URL `account/groups`.  Why did I pick this name?  Well, it's neat, and it explains precisely what it is that we're trying to access - a list of *groups* for my *account*.  We can create a simple route like this:

```
$app->get('/account/groups/?', function () use ($app) {   
	echo "For Aiur!";
});
```

`$app->get` tells us that we're creating a `GET` route, because when anyone navigates to a web page in their browser, they are really issuing a `GET` request to the web server.  Then we specify the URL we would like to route. The `?` at the end of the route means that the trailing `/` is optional.  

`function () use ($app)` defines a `callback`, which is basically a function that we can pass into another function as a parameter.  This callback will contain all the code that should be executed when someone navigates to this URL.

So, let's visit our new route:

![UserFrosting - creating a new page - 1]({{site.url}}/img/tutorials/new-page-1.png)

Alright, but not very impressive.  All we did was `echo` a single line, which hopefully you already know how to do.  Let's try to make this into a UserFrosting page!

### Headers, Footers, and Navbars (oh my!)

To do this, we need to create a [template]({{site.url}}/navigating/#twig).  Templates are stored in `userfrosting/templates/`.  Since we're creating a page for authenticated users, we'll make this a themed page.  This will apply the logged-in user's theme to the style and layout of this page.  In `userfrosting/templates/themes/default/`, create a new HTML file called `account-groups.html`.  Add the following HTML:

```
<!DOCTYPE html>
<html lang="en">
{% include 'components/head.html' %}
  
<body>
    <div id="wrapper">
        {% include 'components/nav-account.html' %}
        <div id="page-wrapper">
            {% include 'components/alerts.html' %}
            
            <h1>The user's ID is {{user.id}}.</h1>
            
            {% include 'components/footer.html' %}    
        </div>
    </div>
</body>
</html>
```

This is a basic, (nearly) empty user account page.  We've included `components/head.html`, which contains the `<head>` tag and various CSS includes, `components/nav-account.html`, which renders the top and side navigation bars, and `components/footer.html`, which contains a footer message and various Javascript includes.  `components/alerts.html` will display any messages that have been placed in the **message stream**.

The line 

`<h1>The user's ID is {{user.id}}.</h1>`

is our first bit of actual content.  `{{user.id}}` is a [Twig variable](http://twig.sensiolabs.org/doc/templates.html).  For convenience, UserFrosting automatically loads the current user into the Twig variable `user`.  So, you can always access any property of the current user from Twig by using `{{user.*}}`.

Going back to our route in `index.php`, let's make UserFrosting render our new template!

Change your new route to look like this:

```
$app->get('/account/groups/?', function () use ($app) {   
   $app->render('account-groups.html', [
       'page' => [
           'author' =>         $app->site->author,
           'title' =>          "Account Groups",
           'description' =>    "A list of the groups to which you belong.",
           'alerts' =>         $app->alerts->getAndClearMessages()
       ]
   ]);  
});
```

This is telling UserFrosting to tell Twig to look in `userfrosting/templates/themes/default/` for a template called `account-groups.html`, and render it with the specified author, title, page description, and any messages that have been placed into the message stream.

If you're logged in, it should look like this:

![UserFrosting - creating a new page - 2]({{site.url}}/img/tutorials/new-page-2.png)

Ok, now let's see if we can pull the group names for the user and print them nicely.  Change your template file to look like this:

```
<!DOCTYPE html>
<html lang="en">
{% include 'components/head.html' %}
  
<body>
    <div id="wrapper">
        {% include 'components/nav-account.html' %}
        <div id="page-wrapper">
            {% include 'components/alerts.html' %}
            
            <h1>User Groups</h1>
            <ul class="list-group">
            {% for group in groups %}
                <li class="list-group-item">
                      <i class="{{group.icon}} fa-fw"></i> {{group.name}}
                </li>
            {% endfor %}
            </ul>
            <br>
            {% include 'components/footer.html' %}    
        </div>
    </div>
</body>
</html>
```

The `{% for ... %}` tag is Twig's syntax for a `for` loop, which you close with a `{% endfor %}` tag.  `groups` is a Twig variable, which we now need to define in our route.  So now, change your route to:

```
$app->get('/account/groups/?', function () use ($app) {   
   $app->render('account-groups.html', [
       'page' => [
           'author' =>         $app->site->author,
           'title' =>          "Account Groups",
           'description' =>    "A list of the groups to which you belong.",
           'alerts' =>         $app->alerts->getAndClearMessages()
       ],
       'groups' => $app->user->getGroups()
   ]);  
});
```

That extra line `$app->user->getGroups()` loads a list of all the groups (as `Group` objects) that the currently logged in user (`$app->user`) belongs to, and passes them into the Twig variable `groups`.  

Great, now your page should look like this:

![UserFrosting - creating a new page - 3]({{site.url}}/img/tutorials/new-page-3.png)

### Access Control

Alright, one last thing.  We need to control who can access this page!  As it stands, this is a **publicly accessible page**.  We can control access to the page through an **authorization hook**, like so:

```
$app->get('/account/groups/?', function () use ($app) {   
   // Access-controlled page
   if (!$app->user->checkAccess('uri_account-groups')){
       $app->notFound();
   }   
   
   $app->render('account-groups.html', [
       'page' => [
           'author' =>         $app->site->author,
           'title' =>          "Account Groups",
           'description' =>    "A list of the groups to which you belong.",
           'alerts' =>         $app->alerts->getAndClearMessages()
       ],
       'groups' => $app->user->getGroups()
   ]);  
});
```

The function `$app->user->checkAccess` checks whether or not the current user has access to the authorization hook `uri_account-groups`, and returns a 404 error if they don't.  

To define the hook, and specify which users and groups have access to it, we need to add it to our database.  Specifically, we need to modify either `uf_authorize_group` or `uf_authorize_user`.  

Let's suppose we want to allow access to this page for an entire group.  Then we go into `uf_authorize_group` (for example, through phpMyAdmin), and insert a record:

| id  | group_id | hook | conditions |
| ------------- | ------------- | ------------- | ------------- |
| x | 1 | uri_account-groups | always() |

`group_id` is the group that we are authorizing access for, for example, the `Users` group.  `uri_account-groups` is the hook that we are defining, and `conditions` lets us use an expression to limit the conditions under which members of this group have access.  To always allow access for this group, simply use the condition `always()`.

Cool!  Now anyone who is a member of group 1, that is the `Users` group, will be able to access your new page.  Anyone who isn't a member, including unauthenticated users, will be shown a 404 error page.

### Linking to Your Page in the Navbar

What's that, you want your page to show up in the sidebar?  Well, ok.  

Head on over to  `userfrosting/templates/themes/default/menus/sidebar.html`.  You'll see some links like:

```
{% if checkAccess('uri_users') %}
<li>
  <a href="{{site.uri.public}}/users"><i class="fa fa-users fa-fw"></i> {{ translate("MENU_USERS") }}</a>
</li>
{% endif %}
```

The `checkAccess` function in Twig works just like `$app->user->checkAccess`.  This will let you display a menu item only when the user has access to that page.

`{{site.uri.public}}` is the root URL of your site, which UserFrosting automatically detects and loads into the global `site` variable.

The `translate` function allows for multilanguage support.  If you want to have different versions of the text for this menu item depending on the user's locale, you can define a **message id** in the various language files in `userfrosting/locales`, and `translate` will select the appropriate message for the current user's locale.  If you don't care about multilanguage support, you can simply put the text here that you'd like the menu item to appear as:

```
{% if checkAccess('uri_account-groups') %}
<li>
  <a href="{{site.uri.public}}/account/groups"><i class="fa fa-tags fa-fw"></i> My Groups</a>
</li>
{% endif %}
```

And that's it!  I hope this was helpful!
