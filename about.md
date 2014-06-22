---
layout: default
title: "UserFrosting: About"
---   

#About

Welcome to UserFrosting, a secure, modern user management system for web services and applications.  UserFrosting is based on the popular UserCake system, written in PHP.  UserFrosting improves on this system by adding a sleek, intuitive frontend interface based on HTML5 and Twitter Bootstrap.  We've also separated the backend PHP machinery that interacts with the database from the frontend code base.  The frontend and backend talk to each other via AJAX and JSON.

## Why UserFrosting?

This project grew out of a need for a simple user management system for my tutoring business, [Bloomington Tutors](http://bloomingtontutors.com).  I wanted something that I could develop rapidly and easily customize for the needs of my business.  Since my [prior web development experience](http://alexanderweissman.com/completed-projects/) was in pure PHP, I decided to go with the PHP-based UserCake system.  Over time I modified and expanded the codebase, turning it into the UserFrosting project. 

## Why is the new version called "butterflyknife"?

When a caterpillar undergoes metamorphosis, it liquifies all of its internal organs inside its cocoon, rearranging the bits and pieces to build a butterfly.  This is essentially what we have done with the codebase from the the previous version, which was essentially organized the same way as UserCake.  Butterflyknife more cleanly separates code from content, and explicitly distinguishes backend (`api`) pages from the frontend (`account`) pages.  The "knife" part captures the precision control that the new authorization system offers.  Put "butterfly" and "knife" together, and you get the name of a well-known tool which is known for its rapid deployability and elegant design.

## Why not use Node/Drupal/Django/RoR/(insert favorite framework here)?

I chose PHP because PHP is what I know from my prior experience as a web developer. Additionally, PHP remains extremely popular and well-supported.  I chose not to use a framework because I wanted something that I could understand easily and develop rapidly from an existing PHP codebase.
