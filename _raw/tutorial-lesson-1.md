One of the simplest tasks in extending UserFrosting is to create a new page. If you're a beginner, you're probably used to creating a single `.php` file which contains the content of your page (e.g. `barracks.php`), that you then view by navigating to `http://mysite.com/barracks.php`.

UserFrosting on the other hand uses the **front controller pattern**, which gives you more flexibility and decouples the URLs for your pages from the actual code that generates their content. This means that the code for generating a page is no longer contained to a single file. For more information about how this works, please see [Front Controllers and the Slim Microframework]({{site.url}}/navigating/#slim).

For this tutorial, we will create a simple page that lists all of the **groups** to which the current user belongs.

Let's start by creating the main route in `public/index.php`. You will see many blocks of code that look like this:

            $app->get('/dashboard/?', function () use ($app) {    
                // Access-controlled page
                error_log("Checking access");
                if (!$app->user->checkAccess('uri_dashboard')){
                    $app->notFound();
                }

                $app->render('dashboard.twig', []);          
            });

These are **routes**, which [Slim](www.slimframework.com) uses to decide what to do when a visitor to your site goes to a particular URL. This route handles the `/dashboard` URL.

Let's create a route for our new page, right below this block of code. Let's call the URL `account/groups`. Why did I pick this name? Well, it's neat, and it explains precisely what it is that we're trying to access - a list of _groups_ for my _account_. We can create a simple route like this:

            $app->get('/account/groups/?', function () use ($app) {   
                echo "For Aiur!";
            });

`$app->get` tells us that we're creating a `GET` route, because when anyone navigates to a web page in their browser, they are really issuing a `GET` request to the web server. Then we specify the URL we would like to route. The `?` at the end of the route means that the trailing `/` is optional.

`function () use ($app)` defines a `callback`, which is basically a function that we can pass into another function as a parameter. This callback will contain all the code that should be executed when someone navigates to this URL.

So, let's visit our new route:

![UserFrosting - creating a new page - 1]({{site.url}}/img/tutorials/new-page-1.png)

Alright, but not very impressive. All we did was `echo` a single line, which hopefully you already know how to do. Let's try to make this into a UserFrosting page!

<section class="tutorial">

## Extending a Template Layout

To create a page, we need to create a [template]({{site.url}}/navigating/#twig). Templates are stored in `userfrosting/templates/themes/`, which contains subdirectories for each theme. We'll talk more about themes later, but in general you'll want to add new content to the `default` theme. In `userfrosting/templates/themes/default/`, create a new HTML file called `account-groups.twig`. The `.twig` extension means that this is a Twig template, which gives us some powerful formatting and content reuse capabilities (more about Twig later). Add the following HTML:

            {% raw %}
				{% extends "layouts/layout-dashboard.twig" %}
				{% set page_group = "dashboard" %}

				{% block page %}   
				    {% set page = page | merge({
				        "title"       : "Account Groups",
				        "description" : "A list of the groups to which you belong."
				    }) %}
				    {{ parent() }}
				{% endblock %}

				{% block content %}
				    <h1>The user's ID is {{user.id}}.</h1>
				{% endblock %}
            {% endraw %}

This is a basic, (nearly) empty user account page.  Let's take a look at how this works.  Our first line,

`{% raw %}{% extends "layouts/layout-dashboard.twig" %}{% endraw %}`

tells Twig which layout to use for this page.  Here, we've chosen to use the "dashboard" layout, which automatically adds the HTML `<head>`, top and side navigation bars, footer, and alert box, which will display any messages that have been placed in the **message stream**.  

Think of layouts as a type of template, which provides blanks where we can fill in some content.  When we `extend` a layout, we are defining what we goes into those blanks.  In Twig, these blanks are called **blocks**.  To create content to be filled into a specific block, simply wrap it in the `{% raw %}{% block ... %}...{% endblock %}{% endraw %}` tags.

Our `content` block is the main block where all of our content should reside, between the navigation bars and the page footer.  Other blocks available in this layout are:

- `head` - Useful if we want to override the content in the `<head>` element.
- `fragments` - Content that is not part of the main content block, but that we might display via Javascript.  For example, the TOS modal popup on the registration page. 
- `page_scripts` - Page-specific `<script>` blocks go here.
- `page` - This is a special block that envelopes the entire page.  Generally, we don't put HTML content directly in here here.  Instead, we use it as a convenient place to define page-wide properties, such as the page title, description, and other variables that need to be set before rendering the page.  You'll notice in v0.3.0 we had to set these variables in the controller - now with 0.3.1, we can set them directly in the template!

You may, of course, define your own layouts - simply add them to the `layouts` directory.  To read more about template inheritance, see the Twig documentation on [extends](http://twig.sensiolabs.org/doc/tags/extends.html).

The line

`{% raw %}<h1>The user's ID is {{user.id}}.</h1>{% endraw %}`

is our first bit of actual content. `{% raw %}{{user.id}}{% endraw %}` is a [Twig variable](http://twig.sensiolabs.org/doc/templates.html). For convenience, UserFrosting automatically loads the current user into the Twig variable `user`. So, you can always access any property of the current user from Twig by using `{% raw %}{{user.*}}{% endraw %}`.

Going back to our route in `index.php`, let's make UserFrosting render our new template!

Change your new route to look like this:

            $app->get('/account/groups/?', function () use ($app) {   
               $app->render('account-groups.twig', []);  
            });

This is telling UserFrosting to tell Twig to look in `userfrosting/templates/themes/default/` for a template called `account-groups.twig`, and render it.  The second array argument allows us to pass in any additional variables to be rendered in the corresponding placeholders on the page.

If you're logged in, it should look like this:

![UserFrosting - creating a new page - 2]({{site.url}}/img/tutorials/new-page-2.png)

Ok, now let's see if we can pull the group names for the user and print them nicely. Change your template file to look like this:

            {% raw %}
				{% extends "layouts/layout-dashboard.twig" %}
				{% set page_group = "dashboard" %}

				{# Set page properties (page.*) here. #}
				{% block page %}
				    {# By putting this in a special block, we ensure that it will be set AFTER the default values are set in the parent template, 
				    but BEFORE the page itself is rendered. #}    
				    {% set page = page | merge({
				        "title"       : "Dashboard",
				        "description" : "Your user dashboard."
				    }) %}
				    {{ parent() }}
				{% endblock %}

				{% block content %}
				    <h1>User Groups</h1>
				    <ul class="list-group">
				    {% for group in groups %}
				        <li class="list-group-item">
				              <i class="{{group.icon}} fa-fw"></i> {{group.name}}
				        </li>
				    {% endfor %}
				    </ul>
				{% endblock %}
            {% endraw %}

The `{% raw %}{% for ... %}{% endraw %}` tag is Twig's syntax for a `for` loop, which you close with a `{% raw %}{% endfor %}{% endraw %}` tag. `groups` is a Twig variable, which we now need to define in our route. So now, change your route to:

            $app->get('/account/groups/?', function () use ($app) {   
               $app->render('account-groups.twig', [
                   'groups' => $app->user->getGroups()
               ]);  
            });

That extra line `$app->user->getGroups()` loads a list of all the groups (as `Group` objects) that the currently logged in user (`$app->user`) belongs to, and passes them into the Twig variable `groups`.

Great, now your page should look like this:

![UserFrosting - creating a new page - 3]({{site.url}}/img/tutorials/new-page-3.png)

</section>

<section class="tutorial">

## Access Control

Alright, one last thing. We need to control who can access this page! As it stands, this is a **publicly accessible page**. We can control access to the page through an **authorization hook**, like so:

            $app->get('/account/groups/?', function () use ($app) {   
               // Access-controlled page
               if (!$app->user->checkAccess('uri_account-groups')){
                   $app->notFound();
               }   

               $app->render('account-groups.twig', [
                   'groups' => $app->user->getGroups()
               ]);
            });

The function `$app->user->checkAccess` checks whether or not the current user has access to the authorization hook `uri_account-groups`, and returns a 404 error if they don't.

To define the hook, and specify which users and groups have access to it, we need to add it to our database. The administrative interface actually provides a handy tool for us to do this.  To add a rule for a group, log in as the root user and go to **Configuration->Groups**.  Here you will see a list of all user groups.

Next to each group you will see an "Actions" menu.  In the Actions menu for group `Users`, select "Authorization rules".  Click "create new rule" at the bottom, and a form will pop up.

Enter `uri_account-groups` as the name of the hook.  For "conditions", enter `always()`.  This means that users in group "Users" will have unlimited access to the  `uri_account-groups` hook.  For more advanced and fine-grained access control, we can use boolean expressions consisting of `AccessCondition`s to limit the scope of access that we grant.

Cool! Now anyone who is a member of group 1, that is the `Users` group, will be able to access your new page. Anyone who isn't a member, including unauthenticated users, will be shown a 404 error page.

</section>

<section class="tutorial">

## Linking to Your Page in the Navbar

What's that, you want your page to show up in the sidebar? Well, ok.

Head on over to `userfrosting/templates/themes/default/components/dashboard/menus/sidebar.twig`. You'll see some links like:

            {% raw %}
            {% if checkAccess('uri_users') %}
            <li>
              <a href="{{site.uri.public}}/users"><i class="fa fa-users fa-fw"></i> {{ translate("MENU_USERS") }}</a>
            </li>
            {% endif %}
            {% endraw %}

The `checkAccess` function in Twig works just like `$app->user->checkAccess`. This will let you display a menu item only when the user has access to that page.

`{% raw %}{{site.uri.public}}{% endraw %}` is the root URL of your site, which UserFrosting automatically detects and loads into the global `site` variable.

The `translate` function allows for multilanguage support. If you want to have different versions of the text for this menu item depending on the user's locale, you can define a **message id** in the various language files in `userfrosting/locales`, and `translate` will select the appropriate message for the current user's locale. If you don't care about multilanguage support, you can simply put the text here that you'd like the menu item to appear as:

            {% raw %}
            {% if checkAccess('uri_account-groups') %}
            <li>
              <a href="{{site.uri.public}}/account/groups"><i class="fa fa-tags fa-fw"></i> My Groups</a>
            </li>
            {% endif %}
            {% endraw %}

And that's it! I hope this was helpful!

</section>