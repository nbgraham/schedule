schedule
========

A utility for viewing sections and when they are scheduled.

### setup

1. Make sure php's cli config has a high enough memory_limit - 512mb is the minimum recommended value.
2. Open Terminal
3. `git clone git@github.com:ashinpaugh/schedule.git schedule`
4. `composer install -a`
5. See Command `schedule:setup`.

### commands:
    php bin/console schedule:setup --import

Combines several commands into one for easy deployment.

- Creates the app's database and table structure.
- Creates production related assets.
- Optimizes the app's autoloader.
- Optional `--import`. Executes `schedule:import` if present.

````
php bin/console schedule:import -n --purge-with-truncate --no-debug --source=(ods/book) --year=2015
````

Triggers the import driver to bring in data from the provided `--source`.

`--source=ods`

Optional - either `ods` or `book`. The source the Import Driver should pull from.

`--year=2016`

Optional - four digit year. The starting year to import. If omitted the app will
take the current year and subtract `num_years` to get the starting year to import.


### deployments in http://casapps-dev.ou.edu/classplan/

````
cd /var/www/html/classplan
docker exec -it web /var/www/html/classplan/bin/console assetic:dump --env=prod
docker exec -it web /var/www/html/classplan/bin/console cache:clear --no-warmup --env=prod
docker exec -it web /var/www/html/classplan/bin/console cache:warmup --env=prod
docker exec -it web mkdir /var/www/html/classplan/var/cache/prod/http_cache
docker exec -it web chmod o=rwx /var/www/html/classplan/var/cache/prod/
docker exec -it web chmod o=rwx /var/www/html/classplan/var/cache/prod/jms_serializer
````


### cache
The application leverages Symfony's reverse proxy in dev and prod.

In dev if the GET param ?no_cache=1 is passed in the URL, the reverse proxy is ignored.  

Section API calls are cached for ten minutes, and can be tweaked  in the @Cache() annotation.

    /**
     * Fetch a subset of sections based on the provided filter criteria.
     * 
     * ...
     *
     * @Cache(public=true, expires="+10 minutes", maxage=600, smaxage=600)
     */

Assets are handled by Assetic. They are first dumped to their output file location (ie: web/assets/compiled/(css|js)).
Most are then inlined and cached along with the rest of the page in http_cache.
Since the University doesn't use CDNs / Varnish this was the fastest way to deliver those assets.


A Symfony project created on March 7, 2017, 11:37 am.
