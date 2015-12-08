Ok, so you've patiently completed lessons [1]({{site.url}}/tutorials/lesson-1-new-page) and [2]({{site.url}}/tutorials/lesson-2-process-form), and hopefully you're starting to understand the basic concepts of the [model-view-controller (MVC)](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) architecture.  We've dived a bit into Twig, which handles the "view" part of "model-view-controller".  

We've also talked a lot about controllers, using them to send data **to** the view (to render a "page"), and also capturing data **from** `POST` requests to perform some operations on our data model (i.e., deleting a user, updating the name of a group, etc).

What we *haven't* really talked about yet is the **model**.  We've interacted with it briefly, though.  In lesson 1 we used `$app->user`, which represents the currently logged-in user with an instance of the `User` class, to get a list of groups for that user.  In lesson 2, we used the Eloquent query builder to obtain collections of `User` and `Group` objects.  We then modified these objects and used the `->save()` method to update their information in the database.

But of course, your application probably consists of more than just users and groups!  After all, your users are probably supposed to *do* something in your system, and interact with other sorts of data.  So, how do we extend the UserFrosting data model to include, for example, `Transactions` or `Events` or `Cats` or `NuclearMissiles`?

In this tutorial, we will create a new type of data object, `StaffEvent`, which will represent events such as meetings, parties, luncheons, etc.  We will design our model so that our users can be assigned to these events.

## Setting up the Database Tables

First, we need to represent events in our database.  To do this, we will manually create a table, `staff_event`, where each row will represent a unique event and its properties.  We will define the following columns for this table as well:

- `id` (int, primary key, autoincrement, unique, not null)
- `date` (datetime, not null)
- `location` (text)
- `description` (text)
- `created_at` (timestamp)
- `updated_at` (timestamp)

**In UserFrosting, every table must have a primary key called `id`!**  UserFrosting's base classes for loading and manipulating the database will not work otherwise.

Hopefully, you know how to create a database table in whichever system you are using.  If not, please consult your system's documentation.

A user can be assigned to more than one event, and events can have more than one user.  Thus, this constitutes a **many-to-many** relationship.  To model a many-to-many relationship, we need an additional table called the **link table**.  We will name the table using the convention of placing an underscore between the names of the tables to be linked.  Thus, we will call this table `staff_event_user`.  This table will have three columns:

- `id` (int, primary key, autoincrement, unique, not null)
- `user_id` (int, not null)
- `event_id` (int, not null)

Great!  We're now ready to register our tables in UserFrosting.

## Registering the New Tables

To work with our new tables in UserFrosting, we need two pieces of information: the names of the tables, and the names of their columns.  We will register this information all in one place in the code, in `userfrosting/initialize.php`.

To do this, we will first create a new instance of the `DatabaseTable` class:

```
$table_staff_event = new \UserFrosting\DatabaseTable("staff_event", [
    "date",
    "location",
    "description",
    "created_at",
    "updated_at"
]);
```

Notice that the first argument is the name of the table, and the second argument is an array containing the names of the columns.  We don't need to specify the `id` column, because UserFrosting implicitly assumes that this column exists.

The reason we need to specify the names of the columns in the code is mainly for security.  When UF's base classes execute a SQL query, they must interpolate the names of the columns into the query.  This is because [prepared statements do not support parameterization of table and column names](http://stackoverflow.com/a/182353/2970321). 

Thus, the array of column names serves as a **whitelist**, telling UF which column names are valid before it prepares a query.  Thus, `location` will be seen as a valid column name, while `1;DROP TABLE users` will not (and UF will throw an Exception).

Once we define our `DatabaseTable` object, we can register it with UserFrosting:

```
\UserFrosting\Database::setSchemaTable("staff_event", $table_staff_event);
```

The first parameter for `setSchemaTable ` is simply a **handle** that we will use to refer to the table throughout the code.  The convention is to give it the same name as the table itself, but you may name it however you like.  The second parameter is the `DatabaseTable` object that we just created, which will be registered with that handle.

We will do the same thing for the link table, but with one minor difference - we won't bother specifying the column names.  This is because we usually will not model the rows in our link table as data objects, like we will do with events.  Instead, [CRUD](https://en.wikipedia.org/wiki/Create,_read,_update_and_delete) for the link table will be managed through the data objects that it links. 

All we will do for the link table, then, is register the table name:

```
$table_staff_event_user = new \UserFrosting\DatabaseTable("staff_event_user");
\UserFrosting\Database:: setSchemaTable("staff_event_user", $table_staff_event_user);
```

Great, now we have an organized, sane way to access information **about** our tables (but not the data in those tables, yet).

## Modeling the Event Object

You may be used to simply writing and executing a SQL query every time you need to interact with the database, or perhaps writing functions that encapsulate this behavior.  In UserFrosting, we will go one step further and use **objects** to encapsulate all the information about a particular row in the database, along with the functionality to modify and store it in the database.  This gives us a uniform, consistent way to represent and manipulate data without repetitive code.

If you look in the `userfrosting/models` directory, you will notice classes called `Group` and `User`.  These are the classes used to model groups and users, and they both inherit the basic functionality of their base class, `UFModel`, which itself inherits from Eloquent's [`Model`](http://laravel.com/docs/5.0/eloquent#basic-usage) class.  If you don't know what "inherit" means, now is a good time to [learn a little about object-oriented programming](https://en.wikipedia.org/wiki/Class-based_programming#Inheritance).

We will create a new class, in a new file, called `StaffEvent`, which will also inherit from UFModel:

**userfrosting/models/mysql/StaffEvent.php**

```
<?php

namespace UserFrosting;

use \Illuminate\Database\Capsule\Manager as Capsule;

class StaffEvent extends UFModel {

    protected static $_table_id = "staff_event";
    
}
```

**Note that since we are creating a new class, you must have Composer installed and run `composer update` to have your new class autoloaded.**  See [here]({{site.url}}/navigating/#composer) for more information.

Notice that we set a static `$_table_id` property in our class.  UserFrosting will use this to look up the `DatabaseTable` object containing the information about our table based on the handle that we assigned it in `initialize.php`.  

With just these few lines, we can create new `StaffEvent` objects easily:

```
$new_event = new StaffEvent([
    "date" => "2015-12-24 14:00:00",
    "location" => "Room 101",
    "description" => "Mandatory Christmas party for all employees!"
]);
```

And we can store the new event to the database:

```
$new_event->save();
$id = $new_event->id;
```

Notice how all the SQL queries are taken care of for us.  Event objects can also be used to update properties in the table:

```
$new_event->location = "Torture Chamber Alpha";
$new_event->save();
```

And we can even delete events from the database:

```
$new_event->delete();
```

Ok, so that covers the "C", "U", and "D" in CRUD.  But what about the "R" (read)?  

Well, it turns out that our new class `StaffEvent` is also a query builder!  Want to get a list of all staff events in december, sorted by date?  No problem:

```
$december_events = StaffEvent::whereBetween('date', [
		"2015-12-01 00:00:00",
		"2016-01-01 00:00:00"
	])
	->get();
```

## Modeling Relationships

That covers the basic CRUD operations for events.  But, we still haven't modeled the relationships **between** events and users!  An event can have multiple users, and a user can have multiple events.  It would be nice to be able to take a given `StaffEvent` object, and get an array of all users who are assigned to that event.  Or, we might want to take a specific `User` object, and get an array of all events associated with that user.  To do this, we will modify the `StaffEvent` and `User` objects.

First, we have to ask ourselves some questions.  When we load a particular `StaffEvent` from the database, do we want to immediately load all of its assigned users?  Or, should we wait until we actually need them?

The second method is commonly called **lazy loading**, and is the method that I prefer.  Why?  Because it saves us unnecessary querying.  If in a given request, we don't care about the users assigned to an event, then we won't waste time querying the database for that information.

To implement lazy loading, all we need to do is implement a method in `StaffEvent` called `users`:

```
public function users(){
	$link_table = Database::getSchemaTable('staff_event_user')->name;
   return $this->belongsToMany('UserFrosting\User', $link_table);
}
```

This is Eloquent's way of providing access to a many-to-many relationship.  All we need to go is get the name of the link table from our database schema, and then call `belongsToMany` on the current `StaffEvent` object.   The first argument is the name of the class that models our User object, and the second argument is the name of our link table.  This method will then return a collection of User objects for a particular `StaffEvent`.

We can also add this functionality to the [magic getter](http://php.net/manual/en/language.oop5.magic.php), by overloading the `__get` and `__isset` methods in `StaffEvent`:

```
public function __get($name){
   if ($name == "users")
       return $this->users();
   else
       return parent::__get($name);
}

public function __isset($name){
   if ($name == "users")
       return true;
   else
       return parent::__isset($name);
}
```

This will let us access an event's users by simply calling `$users = $my_event->users;`.  Overloading the magic methods will also allow us to access this data in Twig.  If we pass a `StaffEvent` object into a Twig template through our call to `render`, then we can access the array of users for that event via: `{{my_event.users}}`.  Nifty, eh?

### Refreshing Related Data

Ok, but what if I modify my `user` table after I call `users`?  Fortunately, Eloquent provides the `fresh` method as an easy way for us to reload an object's data from the database, along with any relationships we care about:

```
$my_event = $my_event->fresh(['users']);
```

Thus, we can do things like:

```
// Fetch event 1
$event = StaffEvent::find(1);

// Load users for event 1
$users = $event->users();

// Change one of those users
$users[0]->title = "The New Kid in Town";
$users[0]->save();

// Refresh the event, updating the user info
$event = $event->fresh(['users']));
```

### Modifying Relationships

Ok, so we've seen how to get the related users for an event, but how do we add and remove related users?  To do this, we can use the `attach` and `detach` methods:

```
// Fetch user 1
$user = User::find(1);

// Fetch event 1
$event = StaffEvent::find(1);

// Associate user 1 with event 1
$event->users()->attach($user->id);

// De-associate user 1 with event 1
$event->users()->detach($user->id);

```

We might want to automatically delete any relationships when we delete a particular object.  So, we will overload the `delete` method in `StaffEvent`:

```
public function delete(){        
   // Remove all user associations
   $this->users()->detach();
   
   // Delete the event itself        
   $result = parent::delete();
   
   return $result;
}
```

And that's it!  Our `StaffEvent` data is now a full-fledged, relational data model that can keep track of which users are assigned to it in a sane manner.  We can make the same modifications to `User` to create methods like `events()`, etc as necessary.

Combining what you've learned here with what you learned in Lesson 2, you should now be able to implement a controller with all the routes you need to create, update, delete, and view/list events, as well as assign users to events.
