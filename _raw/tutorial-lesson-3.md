Ok, so you've patiently completed lessons [1]({{site.url}}/tutorials/lesson-1-new-page) and [2]({{site.url}}/tutorials/lesson-2-process-form), and hopefully you're starting to understand the basic concepts of the [model-view-controller (MVC)](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) architecture.  We've dived a bit into Twig, which handles the "view" part of "model-view-controller".  

We've also talked a lot about controllers, using them to send data **to** the view (to render a "page"), and also capturing data **from** `POST` requests to perform some operations on our data model (i.e., deleting a user, updating the name of a group, etc).

What we *haven't* really talked about yet is the **model**.  We've interacted with it briefly, though.  In lesson 1 we used `$app->user`, which represents the currently logged-in user with an instance of the `MySqlUser` object, to get a list of groups for that user.  In lesson 2, we used the `MySqlUserLoader` class to fetch a list of `MySqlUser` objects.  We then modified these objects and used the `->store()` method to update their information in the database.

But of course, your application probably consists of more than just users and groups!  After all, your users are probably supposed to *do* something in your system, and interact with other sorts of data.  So, how do we extend the UserFrosting data model to include, for example, `Transactions` or `Events` or `Cats` or `NuclearMissiles`?

In this tutorial, we will create a new type of data object, `Event`, which will represent events such as meetings, parties, luncheons, etc.  We will design our model so that our users can be assigned to these events.

## Setting up the Database Tables

First, we need to represent events in our database.  To do this, we will manually create a table, `event`, where each row will represent a unique event and its properties.  We will define the following columns for this table as well:

- `id` (int, primary key, autoincrement, unique, not null)
- `date` (datetime, not null)
- `location` (text)
- `description` (text)

**In UserFrosting, every table must have a primary key called `id`!**  UserFrosting's base classes for loading and manipulating the database will not work otherwise.

Hopefully, you know how to create a database table in whichever system you are using.  If not, please consult your system's documentation.

A user can be assigned to more than one event, and events can have more than one user.  Thus, this constitutes a **many-to-many** relationship.  To model a many-to-many relationship, we need an additional table called the **link table**.  We will name the table using the convention of placing an underscore between the names of the tables to be linked.  Thus, we will call this table `event_user`.  This table will have three columns:

- `id` (int, primary key, autoincrement, unique, not null)
- `user_id` (int, not null)
- `event_id` (int, not null)

Great!  We're now ready to register our tables in UserFrosting.

## Registering the New Tables

To work with our new tables in UserFrosting, we need two pieces of information: the names of the tables, and the names of their columns.  We will register this information all in one place in the code, in `userfrosting/initialize.php`.

To do this, we will first create a new instance of the `DatabaseTable` class:

```
$table_event = new \UserFrosting\DatabaseTable("event", [
    "date",
    "location",
    "description"
]);
```

Notice that the first argument is the name of the table, and the second argument is an array containing the names of the columns.  We don't need to specify the `id` column, because UserFrosting implicitly assumes that this column exists.

The reason we need to specify the names of the columns in the code is mainly for security.  When UF's base classes execute a SQL query, they must interpolate the names of the columns into the query.  This is because [prepared statements do not support parameterization of table and column names](http://stackoverflow.com/a/182353/2970321). 

Thus, the array of column names serves as a **whitelist**, telling UF which column names are valid before it prepares a query.  Thus, `location` will be seen as a valid column name, while `1;DROP TABLE users` will not (and UF will throw an Exception).

Once we define our `DatabaseTable` object, we can register it with UserFrosting:

```
\UserFrosting\Database::setTable("event", $table_event);
```

The first parameter for `setTable` is simply a **handle** that we will use to refer to the table throughout the code.  The convention is to give it the same name as the table itself, but you may name it however you like.  The second parameter is the `DatabaseTable` object that we just created, which will be registered with that handle.

We will do the same thing for the link table, but with one minor difference - we won't bother specifying the column names.  This is because we usually will not model the rows in our link table as data objects, like we will do with events.  Instead, [CRUD](https://en.wikipedia.org/wiki/Create,_read,_update_and_delete) for the link table will be managed through the data objects that it links. 

All we will do for the link table, then, is register the table name:

```
$table_event_user = new \UserFrosting\DatabaseTable("event_user");
\UserFrosting\Database::setTable("event_user", $table_event_user);
```

Great, now we have an organized, sane way to access information **about** our tables (but not the data in those tables, yet).

## Modeling the Event Object

You may be used to simply writing and executing a SQL query every time you need to interact with the database, or perhaps writing functions that encapsulate this behavior.  In UserFrosting, we will go one step further and use **objects** to encapsulate all the information about a particular row in the database, along with the functionality to modify and store it in the database.  This gives us a uniform, consistent way to represent and manipulate data without repetitive code.

If you look in the `userfrosting/models/mysql` directory, you will notice classes called `MySqlGroup` and `MySqlUser`.  These are the classes used to model groups and users, and they both inherit the basic functionality of their base class, `MySqlDatabaseObject`.  If you don't know what "inherit" means, now is a good time to [learn a little about object-oriented programming](https://en.wikipedia.org/wiki/Class-based_programming#Inheritance).

We will create a new class, in a new file, called `MySqlEvent`, which will also inherit from `MySqlDatabaseObject`:

**userfrosting/models/mysql/MySqlEvent.php**

```
<?php

namespace UserFrosting;

class MySqlEvent extends MySqlDatabaseObject {

    ...
}
```

**Note that since we are creating a new class, you must have Composer installed and run `composer update` to have your new class autoloaded.**  See [here]({{site.url}}/navigating/#composer) for more information.

We must then define a constructor for our new class.  For most purposes, all you need to do in the constructor is give the class the information about the `event` table, and then pass any data into the base (parent) class constructor:

```
<?php

namespace UserFrosting;

class MySqlEvent extends MySqlDatabaseObject {

    public function __construct($properties, $id = null) {
        $this->_table = static::getTable('event');
        parent::__construct($properties, $id);
    }
    
}
```

Notice that we use the static `getTable` method to look up the `DatabaseTable` object containing the information about our table based on the handle that we assigned it in `initialize.php`.  We then pass this information to `_table`, which is a member of the base `MySqlDatabaseObject` class and will be used for the basic CRUD operations.

Cool, now we can create new `Event` objects easily:

```
$new_event = new MySqlEvent([
    "date" => "2015-12-24 14:00:00",
    "location" => "Room 101",
    "description" => "Mandatory Christmas party for all employees!"
]);
```

And we can store the new event to the database:

```
$id = $new_event->store();
```

Notice how all the SQL queries are taken care of for us.  Event objects can also be used to update properties in the table:

```
$new_event->location = "Torture Chamber Alpha";
$new_event->store();
```

And we can even delete events from the database:

```
$new_event->delete();
```

Ok, so that covers the "C", "U", and "D" in CRUD.  But what about the "R" (read)?  For this, we will create a special **loader** class, which is basically just a collection of static methods for `SELECT`ing from our `event` table.

## Building the Loader Class

Our `MySqlEvent` class can already do quite a bit, but something it can't do is *load itself from the database*.  Nor should it.  Why not?  Well, suppose we want to load many rows from the `event` table (perhaps the **entire** table) into `MySqlEvent` objects.  If each object were responsible for loading itself, we'd have to do a separate query for each row, which would be terribly inefficient.

Instead, we have a special **loader** class, which can load one or more rows from the database and create the appropriate object(s).  For our example, we will consider the minimum required methods for most applications: checking if a row exists, fetching a single row, and fetching a set of rows.  We'll create a new class, `MySqlEventLoader`, which inherits most of its functionality from `MySqlObjectLoader`:

**userfrosting/models/mysql/MySqlEventLoader.php**

```
<?php

namespace UserFrosting;

class MySqlEventLoader extends MySqlObjectLoader  {
    protected static $_table;       // The table whose rows this class represents. 
        
    public static function exists($value, $name = "id"){
        return parent::fetch($value, $name);
    }
   
    public static function fetch($value, $name = "id"){
        $results = parent::fetch($value, $name);
        
        if ($results)
            return new MySqlEvent($results, $results['id']);
        else
            return false;
    }

    public static function fetchAll($value = null, $name = null){
        $resultArr = parent::fetchAll($value, $name);
        
        $results = [];
        foreach ($resultArr as $id => $group)
            $results[$id] = new MySqlEvent($group, $id);
        return $results;
    }
    
}
```

Note how we've basically just copied the `MySqlGroupLoader` class.  The only difference is that the `fetch` and `fetchAll` methods, instead of creating `Group` objects, now create `MySqlEvent` objects.

Ah, but how does this class know about the layout of the table containing our events?  We need to call `init`, which is a method inherited from the base class.  Back in `initialize.php`, after defining our `DatabaseTable`, we need to do:

`\UserFrosting\MySqlEventLoader::init($table_event);`

Great, now we can do things like:

```
// Fetch a single event by id
$event1 = MySqlEventLoader::fetch("1");

// Fetch a single event by location
$event1 = MySqlEventLoader::fetch("Room 101", "location");

// Fetch all events as an array of MySqlEvent objects
$events = MySqlEventLoader::fetchAll();

// Fetch all events for a specific location, as an array of MySqlEvent objects
$events = MySqlEventLoader::fetchAll("Room 101", "location");
```

If we want to do other types of filtering, like counting events, fetching all events within a specific date range, or filtering by multiple criteria, you will need to create a new method in the `MySqlEventLoader` class.

## Modeling Relationships

That covers the basic CRUD operations for events.  But, we still haven't modeled the relationships **between** events and users!  An event can have multiple users, and a user can have multiple events.  It would be nice to be able to take a given `MySqlEvent` object, and get an array of all users who are assigned to that event.  Or, we might want to take a specific `User` object, and get an array of all events assigned to that user.  To do this, we will modify the `MySqlEvent` and `MySqlUser` objects.

First, we will add a member to the `MySqlEvent` class called `_users`, which will be an array of users who are assigned to this event.

```
<?php

namespace UserFrosting;

protected $_users;

class MySqlEvent extends MySqlDatabaseObject {
    
    ...
}
```

Now, here we have to ask ourselves some questions.  When we load a particular `MySqlEvent` from the database, do we want to immediately load all of its assigned users?  Or, should we wait until we actually need them?

The second method is commonly called **lazy loading**, and is the method that I prefer.  Why?  Because it saves us unnecessary querying.  If in a given request, we don't care about the users assigned to an event, then we won't waste time querying the database for that information.

To implement lazy loading, we will first create a private method in `MySqlEvent` that does the heavy lifting:

```
private function fetchUsers(){
   $db = static::connection();

   $link_table = static::getTable('event_user')->name;
   $object_table = static::getTable('event')->name;
   
   $query = "
       SELECT `$object_table`.*
       FROM `$link_table`, `$object_table`
       WHERE `$link_table`.event_id = :id
       AND `$link_table`.user_id = `$object_table`.id";
   
   $stmt = $db->prepare($query);
   
   $sqlVars[':id'] = $this->_id;
   
   $stmt->execute($sqlVars);
   
   $results = [];
   while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
       $id = $row['id'];
       $results[$id] = new User($row, $row['id']);
   }
   return $results;        
}
```

This is the method that actually generates the array of `User` objects for our event.    Notice how we need to look up both the name of the `event` table, as well as the name of the `event_user` table.  The column names of the link table, `event_id` and `user_id`, are hardcoded into this method.

Next, we'll create a public wrapper method for `fetchUsers`, which we'll call `getUsers`:

```
public function getUsers(){
   if (!isset($this->_users))
       $this->_users = $this->fetchUsers();
       
   return $this->_users;
}
```

So, this function will load the users from the database if it hasn't already been loaded before.  We can also add this functionality to the [magic getter](http://php.net/manual/en/language.oop5.magic.php), by overloading the `__get` and `__isset` methods in `MySqlEvent`:

```
public function __get($name){
   if ($name == "users")
       return $this->getUsers();
   else
       return parent::__get($name);
}

public function __isset($name){
   if ($name == "users")
       return $this->_users ? true : false;
   else
       return parent::__get($name);
}
```

This will let us access an event's users by simply calling `$users = $my_event->users;`.  Overloading the magic methods will also allow us to access this data in Twig.  If we pass a `MySqlEvent` object into a Twig template through our call to `render`, then we can access the array of users for that event via: `{{my_event.users}}`.  Nifty, eh?

### Refreshing Related Data

Ok, but what if I modify my `user` table after I call `getUsers`?  We can overload the `fresh` method, which allows us to reload an object's data from the database, to also reload the associated users:

```
public function fresh(){
   $event = new MySqlEvent(parent::fresh(), $this->_id);
   $event->_users = $this->fetchUsers();
   return $event;
}
```

Then we can do things like:

```
// Fetch event 1
$event = MySqlEventLoader::fetch("1");

// Load users for event 1
$users = $event->users;

// Change one of those users
$users[0]->title = "The New Kid in Town";

// Refresh the event, updating the user info
$event = $event->fresh();
```

### Modifying Relationships

Ok, so we've seen how to get the related users for an event, but how do we add and remove relationships?  To do this, we will create `addUser` and `removeUser` methods:

```
public function addUser($user_id){
   // First, load current users for event
   $this->getUsers();
   // Return if user already in event
   if (isset($this->_users[$user_id]))
       return $this;
   
   // Next, check that the requested user actually exists
   if (!UserLoader::exists($user_id))
       throw new \Exception("The specified user_id ($user_id) does not exist.");
           
   // Ok, add to the list of users
   $this->_users[$user_id] = UserLoader::fetch($user_id);
   
   return $this;        
}
    
public function removeUser($user_id){
   // First, load current users for event
   $this->getUsers();
   // Return if user not assigned to event
   if (!isset($this->_users[$user_id]))
       return $this;
           
   // Ok, remove from the list of users
   unset($this->_users[$user_id]);
   
   return $this;           
    
}
```

And, we will also need to overload the `store` method for `MySqlEvent` to update the link table whenever we add/remove users:

```
public function store(){
   // Update the event record itself
   parent::store();
   
   // Get the object's associated users
   $this->getUsers();
   
   // Get the event's users as stored in the DB
   $db_users = $this->fetchUsers();

   $link_table = static::getTable('event_user')->name;

   // Add links to any users linked in object that are not in DB yet
   $db = static::connection();
   $query = "
       INSERT INTO `$link_table` (user_id, event_id)
       VALUES (:user_id, :event_id);";
   $stmt = $db->prepare($query);   
   
   foreach ($this->_users as $user_id => $user){       
       // If relationship not in the DB, then add it
       if (!isset($db_users[$user_id])){
           $sqlVars = [
               ':user_id' => $user_id,
               ':event_id' => $this->_id
           ];
           $stmt->execute($sqlVars);
       } 
   }
   
   // Remove any links in DB that are no longer modeled in this object
   if ($db_users){
       $db = static::connection();
       $query = "
           DELETE FROM `$link_table`
           WHERE user_id = :user_id
           AND event_id = :event_id LIMIT 1";
       
       $stmt = $db->prepare($query);          
       foreach ($db_users as $user_id => $user){
           if (!isset($this->_users[$user_id])){
	           $sqlVars = [
	               ':user_id' => $user_id,
	               ':event_id' => $this->_id
	           ];
               $stmt->execute($sqlVars);
           }
       }
   }
   
   // Store function should always return the id of the object
   return $this->_id;
}
```

Finally, we will probably want to automatically delete any relationships when we delete a particular object.  So, we will overload the `delete` method as well, again in `MySqlEvent`:

```
public function delete(){        
   // Can only delete an object where `id` is set
   if (!$this->_id) {
       return false;
   }
   
   // Delete the record itself
   $result = parent::delete();
   
   // Get connection
   $db = static::connection();
   $link_table = static::getTable('event_user')->name;
   
   // Delete the links
   
   $sqlVars[":id"] = $this->_id;
   
   $query = "
       DELETE FROM `$link_table`
       WHERE event_id = :id";
       
   $stmt = $db->prepare($query);
   $stmt->execute($sqlVars);    

   return $result;
}
```

And that's it!  Our `MySqlEvent` data is now a full-fledged, relational data model that can keep track of which users are assigned to it in a sane manner.  We can make the same modifications to `MySqlUser` to create methods like `addEvent`, `removeEvent`, and so forth as necessary.

Combining what you've learned here with what you learned in Lesson 2, you should now be able to implement a controller with all the routes you need to create, update, delete, and view/list events, as well as assign users to events.
