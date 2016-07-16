# Userfrosting Website

## Hosting

The website for Userfrosting is run on [Github Pages](https://pages.github.com/), which means that it is built on [Jekyll](http://jekyllrb.com/).

To run the website code yourself:

1. Make sure you have Jekyll installed.
2. Run `jekyll serve --config _config.yml,_config-dev.yml --watch`

## Website Template

The UserFrosting website is a heavily modified version of [Freelancer](http://startbootstrap.com/template-overviews/freelancer/), by [Start Bootstrap](http://startbootstrap.com/).

Credit for design and layout modifications go to [Sarah Baghdadi](http://pages.iu.edu/~sbaghdad/) and [Alexander Weissman](http://alexanderweissman.com).

## API documentation

To build the API documentation, install [ApiGen]() globally and then run:

`apigen generate --source userfrosting --destination api --exclude *vendor*,*models/auth/password.php* --template-theme "bootstrap"`

from inside the main `userfrosting` project directory. 