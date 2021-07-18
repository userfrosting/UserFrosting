# UserFrosting 4.6

[![Latest Version](https://img.shields.io/github/release/userfrosting/UserFrosting.svg)](https://github.com/userfrosting/UserFrosting/releases)
![PHP Version](https://img.shields.io/badge/php-%5E7.4%20%7C%20%5E8.0-brightgreen)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
[![Join the chat at https://chat.userfrosting.com/channel/support](https://chat.userfrosting.com/api/v1/shield.svg?name=UserFrosting)](https://chat.userfrosting.com/channel/support)
[![Backers on Open Collective](https://opencollective.com/userfrosting/backers/badge.svg)](#backers)
[![Sponsors on Open Collective](https://opencollective.com/userfrosting/sponsors/badge.svg)](#sponsors)
[![Donate](https://img.shields.io/badge/Open%20Collective-Donate-blue.svg)](https://opencollective.com/userfrosting#backer)

| Branch | Version | Build | Coverage | Style |
| ------ |:-------:|:-----:|:--------:|:-----:|
| [master] | ![](https://img.shields.io/github/release/userfrosting/userfrosting.svg?color=success&label=Version) | [![](https://github.com/userfrosting/userfrosting/workflows/Build/badge.svg?branch=master)][UF-Build] | [![](https://codecov.io/gh/userfrosting/userfrosting/branch/master/graph/badge.svg)][UF-Codecov] | [![][style-master]][style] |
| [hotfix] | ![](https://img.shields.io/badge/Version-v4.6.x-yellow.svg) | [![](https://github.com/userfrosting/userfrosting/workflows/Build/badge.svg?branch=hotfix)][UF-Build] | [![](https://codecov.io/gh/userfrosting/userfrosting/branch/hotfix/graph/badge.svg)][UF-Codecov] | [![][style-hotfix]][style] |
| [develop] | ![](https://img.shields.io/badge/Version-v4.7.x-orange.svg) | [![](https://github.com/userfrosting/userfrosting/workflows/Build/badge.svg?branch=develop)][UF-Build] | [![](https://codecov.io/gh/userfrosting/userfrosting/branch/develop/graph/badge.svg)][UF-Codecov] | [![][style-develop]][style] |

<!-- Links -->
[master]: https://github.com/userfrosting/UserFrosting
[hotfix]: https://github.com/userfrosting/UserFrosting/tree/hotfix
[develop]: https://github.com/userfrosting/UserFrosting/tree/develop
[UF-Build]: https://github.com/userfrosting/userfrosting/actions?query=workflow%3ABuild
[UF-Codecov]: https://codecov.io/gh/userfrosting/userfrosting
[style-master]: https://github.styleci.io/repos/18148206/shield?branch=master&style=flat
[style-hotfix]: https://github.styleci.io/repos/18148206/shield?branch=hotfix&style=flat
[style-develop]: https://github.styleci.io/repos/18148206/shield?branch=develop&style=flat
[style]: https://github.styleci.io/repos/18148206

[https://www.userfrosting.com](https://www.userfrosting.com)

If you simply want to show that you like this project, or want to remember it for later, you should **star**, not **fork**, this repository.  Forking is only for when you are ready to create your own copy of the code to work on.

## By [Alex Weissman](https://alexanderweissman.com)

Copyright (c) 2019, free to use in personal and commercial software as per the [license](LICENSE.md).

UserFrosting is a secure, modern user management system written in PHP and built on top of the [Slim Microframework](http://www.slimframework.com/), [Twig](http://twig.sensiolabs.org/) templating engine, and [Eloquent](https://laravel.com/docs/5.8/eloquent#introduction) ORM.

## Features

### User login screen
![User login script](.github/screenshots/login.png)

### User management page
![PHP user management script](.github/screenshots/users.png)

### Permissions management page
![UserFrosting permissions management](.github/screenshots/permissions.png)

## [Demo](https://demo.userfrosting.com)

## Installation

Please see our [installation guide](https://learn.userfrosting.com/installation).

## Troubleshooting

If you are having trouble installing UserFrosting, please [join us in chat](https://chat.userfrosting.com) or try our [forums](https://forums.userfrosting.com).

If you are generally confused about the structure and layout of the code, or it doesn't look like the kind of PHP code that you're used to, please [start from the beginning](https://learn.userfrosting.com/background).

## Mission Objectives

UserFrosting seeks to balance modern programming principles, like DRY and MVC, with a shallow learning curve for new developers.  Our goals are to:

- Create a fully-functioning user management script that can be set up in just a few minutes
- Make it easy for users to quickly adapt the code for their needs
- Introduce novice developers to best practices such as separation of concerns and DRY programming
- Introduce novice developers to modern constructs such as front-end controllers, RESTful URLs, namespacing, and object-oriented modeling
- Build on existing, widely used server- and client-side components
- Clean, consistent, and well-documented code

## Documentation

### [Learning UserFrosting](https://learn.userfrosting.com)

### [API documentation](http://api.userfrosting.com)

### [Change log](CHANGELOG.md)

## Running tests

Run `php bakery test` from the root project directory. Any tests included in `sprinkles/*/tests` will be run.

## Development Team

### Alexander Weissman

Alex is the founder and co-owner of two companies, one that does [math tutoring at Indiana University](https://bloomingtontutors.com) in Bloomington, IN and another company that does [math tutoring at UMD](https://collegeparktutors.com) in College Park, MD. He is a PhD student in the School of Informatics and Computing at Indiana University.

### Louis Charette

Louis's a civil engineer in Montréal, Québec who also has a passion for coding. He is one of the main contributors for SimpsonsCity.com and likes to share his knowledge by helping others the same way he was helped when he first started coding.

### Jordan Mele

Jordan's an Australian Software Engineer at [Canva](https://canva.com). His passion is creating simple yet intuitive software-based solutions for problems that would otherwise be tedious and/or difficult to solve, while keeping the user in control.

### Sarah Baghdadi

Sarah is UserFrosting's UX specialist and frontend designer.  In addition to her work on the UF application itself, she is responsible for the amazing design of https://www.userfrosting.com and https://learn.userfrosting.com.

### Srinivas Nukala

Srinivas's a web applications architect, with a passion for open source technologies. He is experienced in building SaaS (software as a service) web applications and enjoys working on open source projects and contributing to the community. He has a Masters in Computer Science from Pune University, India.

## Contributing

This project exists thanks to all the people who contribute. If you're interested in contributing to the UserFrosting codebase, please see our [contributing guidelines](.github/CONTRIBUTING.md) as well as our [style guidelines](STYLE-GUIDE.md).

[![](https://opencollective.com/userfrosting/contributors.svg?width=890&button=false)](https://github.com/userfrosting/UserFrosting/graphs/contributors)

### Thanks to our translators!

- Louis Charette (@lcharette) - French
- Karuhut Komol (@popiazaza) - Thai
- Pietro Marangon (@Pe46dro) - Italian
- Christian la Forgia (@optiroot) - Italian
- Abdullah Seba (@abdullahseba) - Arabic
- Bruno Silva (@brunomnsilva) - Portuguese
- @BruceGui - Chinese
- @kevinrombach - German
- @rafa31gz - Spanish
- @splitt3r - German
- @X-Anonymous-Y - German
- Dmitriy (@rendername) - Russian
- Amin Akbari (@aminakbari) - Farsi
- Dumblledore - Turkish
- Lena Stergatou (@lenasterg) - Greek

## Supporting UserFrosting

### Backers

Backers help us continue to develop UserFrosting by pledging a regular monthly contribution of $5 or more. [[Become a backer](https://opencollective.com/userfrosting#contribute)]

<a href="https://opencollective.com/userfrosting#backers" target="_blank"><img src="https://opencollective.com/userfrosting/backers.svg?width=890"></a>

#### Sponsors

Support this project by becoming a sponsor. Sponsors have contributed a total of $500 or more to UserFrosting (either as an ongoing backer or one-time contributions). Your logo will show up here with a link to your website. [[Become a sponsor](https://opencollective.com/userfrosting#sponsor)]

[![USOR Games](.github/sponsors/usor.png)](https://usorgames.com)
[![Next Generation Internet](.github/sponsors/nextgi.png)](https://nextgi.com)
