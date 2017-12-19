# Schedule Utility  

## Dependencies

Runtime | Version | More info
---|---|---
**PHP** | v5.6 and above | The php runtime. http://php.net/
**MySQL** | v5.7.* and above | Database for storage. https://www.mysql.com/
**Apache or NGINX** | * | The HTTP server. This document will assume your using Apache.
Composer | V1.4 and above | The project dependency manager utility. https://getcomposer.org/
Git | v2.* and above | Version control https://git-scm.com/
Bower | v1.8.* and above | Web assets dependency manager. https://bower.io/

The bolded dependencies are required for production.

## Introduction

The scheduling utility is the brain child of Dr. Albert Schwarzkopf in an attempt to make the scheduling process at the University of Oklahoma more intuitive and easier to digest. The project was initially developed by Austin Shinpaugh. This document was drafted in hopes that the project will continue to evolve after his graduation. 

The author of the document developed this on OSX and used Homebrew as his package manager. If the next author(s) use another Unix distro then keep in mind that the package manager cli commands will change. For example:

Mac | Unix
--- | ---
`brew install git` | `sudo apt-get install git`

Check your manual for further information based on your distro. In Unix to see if you already have a dependency installed (and if it’s in your PATH environment variable) type:  
`which <dependency>`

This project was developed with [Symfony](https://symfony.com/), a common Model-View-Controller (MVC) PHP framework. It has extensive [documentation](https://symfony.com/doc/current/index.html) available online. This document is intended to combine knowledge from several sources and provided as a general guide.

## Installation

### Basics
Open up the Terminal and download your project dependencies:  
`brew install git`  
`brew install mysql`  
`brew install php56 php56-opcache php56-yaml php56-igbinary php56-mcrypt`  
`brew install httpd24`  
`brew install composer`  
Install in one line:  
`brew install git mysql php56 php56-opcache php56-yaml php56-igbinary php56-mcrypt httpd24 composer`

Several of these will require a first-time setup. It’s recommended you refrain from changing as many defaults as possible.

### Project Setup

#### Part 1: Project setup
Change Directory (CD) into where you’ll be working on the project.  
`cd /Users/ashinpaugh/development/`

Next download the project:  
`git clone schedule git@github.com:ashinpaugh/schedule.git`  
`cd schedule`

The output of pwd is what we will refer to as the Project’s Root Folder (PRF):
`pwd`

Next verify that your basic dependencies are installed correctly, make changes as suggested.  
`php bin/symfony_requirements`

Install the project’s PHP dependencies:  
`composer install`

Next setup the project’s SQL structure:  
`php bin/console doctrine:database:create`
`php bin/console doctrine:schema:create`

Next install the project’s assets into the <Project’s Root Folder>/web directory:  
`php bin/console assetic:dump`

#### Part 2: Server Configuration and Host entry
Setup your Apache Virtual Host (Mac):  
`vim /usr/local/etc/apache2/2.4/extra/httpd-vhosts.conf`

For Unix (creates a new file):  
`sudo vim /etc/apache2/sites-available/scheduler.conf`

Insert the following:
```xml
<VirtualHost *:80>
   ServerAdmin  yourEmail@example.com
   ServerName   scheduler.dev

   SetEnv       APPLICATION_ENV "dev"
   DocumentRoot /Library/WebServer/schedule
   <Directory "/Library/WebServer/schedule">
      DirectoryIndex app_dev.php
      Options        MultiViews FollowSymLinks
      AllowOverride  All
      Require        all granted
   </Directory>
</VirtualHost>
```

Edit server admin to something appropriate.
ServerName will be the URL you type into your browser to access the site.
DocumentRoot see below.

The DocumentRoot can point either to a symlink you create in the WebServer folder that points to your <Project’s Root Folder>/web, or directly to <Project Root Folder>/web. Sometimes there are technical reasons for creating a symlink to that WebServer folder, but we won’t be exploring those in this document.

Edit your host file to point to home:  
`sudo vim /etc/hosts`

And paste:  
`127.0.0.1 scheduler.dev`
	

At this point you should be able to visit the landing page for the app at http://scheduler.dev/app_dev.php/:
![Landing Page](/landing_page.png)

Note the `/app_dev.php` at the end. This means your accessing the “dev” version of the project, which will enable the Symfony’s Web Profiler feature to give you useful information regarding the performance of the app. `/app_dev.php` also loads the “dev” config files and routing resources.

#### Part 3: Development
At this point you need to import data to work with. You can get the data by running:  
`php bin/console scheduler:parse-book`

This wasn’t high optimized (it takes a while to run) as it was hoped that we would be able to hook into API’s provided by OU IT; at the time of writing this document, those have yet to surface.

From here you’re ready to start development. At this point you should familiarize yourself with Symfony’s command line. To get a list of available commands type:  
`php bin/console`  
You should see this list:
```
Symfony 3.3.8 (kernel: app, env: dev, debug: true)

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -e, --env=ENV         The environment name [default: "dev"]
      --no-debug        Switches off debug mode
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  about                                   Displays information about the current project
  help                                    Displays help for a command
  list                                    Lists commands
 api
  api:doc:dump                            Dumps API documentation in various formats
  api:swagger:dump                        Dumps Swagger-compliant API definitions.
 assetic
  assetic:dump                            Dumps all assets to the filesystem
  assetic:watch                           Dumps assets to the filesystem as their source files are modified
 assets
  assets:install                          Installs bundles web assets under a public directory
 cache
  cache:clear                             Clears the cache
  cache:pool:clear                        Clears cache pools
  cache:warmup                            Warms up an empty cache
 config
  config:dump-reference                   Dumps the default configuration for an extension
 debug
  debug:config                            Dumps the current configuration for an extension
  debug:container                         Displays current services for an application
  debug:event-dispatcher                  Displays configured listeners for an application
  debug:router                            Displays current routes for an application
  debug:swiftmailer                       [swiftmailer:debug] Displays current mailers for an application
  debug:translation                       Displays translation messages information
  debug:twig                              Shows a list of twig functions, filters, globals and tests
 doctrine
  doctrine:cache:clear-collection-region  Clear a second-level cache collection region.
  doctrine:cache:clear-entity-region      Clear a second-level cache entity region.
  doctrine:cache:clear-metadata           Clears all metadata cache for an entity manager
  doctrine:cache:clear-query              Clears all query cache for an entity manager
  doctrine:cache:clear-query-region       Clear a second-level cache query region.
  doctrine:cache:clear-result             Clears result cache for an entity manager
  doctrine:database:create                Creates the configured database
  doctrine:database:drop                  Drops the configured database
  doctrine:database:import                Import SQL file(s) directly to Database.
  doctrine:ensure-production-settings     Verify that Doctrine is properly configured for a production environment.
  doctrine:fixtures:load                  Load data fixtures to your database.
  doctrine:generate:crud                  [generate:doctrine:crud] Generates a CRUD based on a Doctrine entity
  doctrine:generate:entities              [generate:doctrine:entities] Generates entity classes and method stubs from your mapping information
  doctrine:generate:entity                [generate:doctrine:entity] Generates a new Doctrine entity inside a bundle
  doctrine:generate:form                  [generate:doctrine:form] Generates a form type class based on a Doctrine entity
  doctrine:mapping:convert                [orm:convert:mapping] Convert mapping information between supported formats.
  doctrine:mapping:import                 Imports mapping information from an existing database
  doctrine:mapping:info                   
  doctrine:query:dql                      Executes arbitrary DQL directly from the command line.
  doctrine:query:sql                      Executes arbitrary SQL directly from the command line.
  doctrine:schema:create                  Executes (or dumps) the SQL needed to generate the database schema
  doctrine:schema:drop                    Executes (or dumps) the SQL needed to drop the current database schema
  doctrine:schema:update                  Executes (or dumps) the SQL needed to update the database schema to match the current mapping metadata.
  doctrine:schema:validate                Validate the mapping files.
 generate
  generate:bundle                         Generates a bundle
  generate:command                        Generates a console command
  generate:controller                     Generates a controller
 lint
  lint:twig                               Lints a template and outputs encountered errors
  lint:xliff                              Lints a XLIFF file and outputs encountered errors
  lint:yaml                               Lints a file and outputs encountered errors
 router
  router:match                            Helps debug routes by simulating a path info match
 schedule
  schedule:import                         Populate the database.
  schedule:parse-book                     Parses the CSV book file and loads its contents into the databse. Deprecated: use the schedule:import command.
  schedule:setup                          Initialize the app settings.
 security
  security:check                          Checks security issues in your project dependencies
  security:encode-password                Encodes a password.
 swiftmailer
  swiftmailer:email:send                  Send simple email message
  swiftmailer:spool:send                  Sends emails from the spool
 translation
  translation:update                      Updates the translation file
```

The author prefers [PHPStorm](https://www.jetbrains.com/phpstorm/) by JetBrains for as an IDE. A one year free trial is available through their website for students. PHPStorm has an extremely useful Symfony plugin that is highly recommend.

When editing your CSS / Javascript run the :watch command in an open terminal window to compile those assets automatically when you reload your page:  
`php bin/console assetic:watch`
To see a list of existing project APIs visit `/api/doc`, ie: http://scheduler.dev/app_dev.php/api/doc
## Maintenance

### Purging Data

Thanks to referential integrity, by deleting one Semester entry all related entries will be taken with it.