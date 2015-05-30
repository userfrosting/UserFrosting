# Fortress

### By Alex Weissman

Copyright (c) 2015

A schema-driven system for whitelisting, sanitizing, and validating raw user input.

## Introduction

Data from the outside world is the Achilles' heel of modern interactive web services and applications.  Code injection, cross-site scripting (XSS), CSRF, and many other types of malicious attacks are successful when a web application accepts user input that it shouldn't have, or fails to neutralize the damaging parts of the input.  Even non-malicious users can inadvertently submit something that breaks your web service, causing it to behave in some unexpected way.

For the sake of both security and quality user experience, it is important for a web developer to do two things:

1. Decide exactly what type of input your application should accept, and;
2. Decide how your application should behave when it receives something that violates those rules.

Sounds simple, right?  Unfortunately, even experienced developers often slip up, allowing a malicious user to execute SQL or PHP on the application's server (or, in the case of XSS and CSRF attacks, allow a user to trick *other users* into executing malicious code).

Part of the problem is that this kind of filtering must be done at every point in the application where the user can submit raw data to the server.  A modern web application might accept hundreds of different types of POST requests, and it can become extremely tedious to code the rules for each request manually.  Much of this work must also be done on both the client side (for user experience) and the server side (for security).

Fortress solves this problem by providing a uniform interface for whitelisting, sanitizing, and validating raw user input.  All you have to do is create a **request schema**, which defines what fields you're expecting the user to submit, and rules for how to handle the contents of those fields.  For example, you might want to strip out all HTML tags from a text box, or check that an email address is well-formed.  The request schema, which is simply a JSON document, makes it easy to manipulate these rules in one place.  The request schema can even be exported to formats compatible with client-side validation libraries such as [FormValidation](http://formvalidation.io/), making it easy to perform client- and server-side validation without having to write every rule twice.

An example request schema, written using the [WDVSS standard](https://github.com/alexweissman/wdvss):

```
{
    "user_name" : {
        "validators" : {
            "length" : {
                "min" : 1,
                "max" : 50,
                "message" : "ACCOUNT_USER_CHAR_LIMIT"
            },
            "required" : {
                "message" : "ACCOUNT_SPECIFY_USERNAME"
            }
        },
        "sanitizers" : {
            "escape" : {}
        }        
    },    
    "email" : {
        "validators" : {
            "required" : {
                "message" : "ACCOUNT_SPECIFY_EMAIL"
            },
            "length" : {
                "min" : 1,
                "max" : 150,
                "message" : "ACCOUNT_EMAIL_CHAR_LIMIT"
            },
            "email" : {
                "message" : "ACCOUNT_INVALID_EMAIL"
            }
        }
    },
    "message" : {
        "default" : "My message", 
        "sanitizers" : {
            "purify" : {}
        }
    },
    "password" : {
        "validators" : {
            "required" : {
                "message" : "ACCOUNT_SPECIFY_PASSWORD"
            },
            "matches" : {
                "field" : "passwordc",
                "message" : "ACCOUNT_PASS_MISMATCH"
            },            
            "length" : {
                "min" : 8,
                "max" : 50,
                "message" : "ACCOUNT_PASS_CHAR_LIMIT"
            }
        },
        "sanitizers" : {
            "raw" : {}
        }    
    },
    "passwordc" : {
        "validators" : {
            "required" : {
                "message" : "ACCOUNT_SPECIFY_PASSWORD"
            },
            "matches" : {
                "field" : "password",
                "message" : "ACCOUNT_PASS_MISMATCH"
            },
            "length" : {
                "min" : 8,
                "max" : 50,
                "message" : "ACCOUNT_PASS_CHAR_LIMIT"
            }
        },
        "sanitizers" : {
            "raw" : {}
        }    
    }
}
```

## Dependencies

- PHP 5.4+
- [Valitron (server-side validation)](https://github.com/vlucas/valitron)
- [HTML Purifier](https://github.com/ezyang/htmlpurifier)

## Installation

### To install with composer:

1. If you haven't already, get [Composer](http://getcomposer.org/) and [install it](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx) - preferably, globally.
2. Require Fortress, either by running `php composer.phar require alexweissman/fortress`, or by creating a `composer.json` file:

```
{
    "require": {
        "php": ">=5.4.0",
        "alexweissman/fortress": "dev-master"
    }
}
```

and running `composer install`.

3. Include the `vendor/autoload.php` file in your project.  For an example of how this can be done, see `fortress/config-fortress.php`.

## Usage

In the definitions of translatable message hooks, the keyword "self" is reserved to refer to the name of the field being validated.  Thus, a message like this:

"MIN_LENGTH" => "The field '{{self}}' must be at least {{min}} characters long"

for a field defined as:

```
"tagline": {
    "validators" : {
        "length" : {
            "min" : 10,
            "message" : "MIN_LENGTH"
        }
    }
}
```

Would translate to:

 "The field 'tagline' must be at least 10 characters long"
 