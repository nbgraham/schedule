schedule
========

A utility for viewing sections and when they are scheduled.

### setup

1. Open Terminal
2. `git clone git@github.com:ashinpaugh/schedule.git schedule`
3. Edit `app/config/parameters.yml` and fill in database related information.
4. `composer install`
5. See Command `schedule:setup`.

### commands:
    php bin/console schedule:setup --import

Combines several commands into one for easy deployment.

- Creates the app's database and table structure.
- Creates production related assets.
- Optimizes the app's autoloader.
- Optional `--import`. Executes `schedule:import` if present.


    php bin/console schedule:import --purge-with-truncate --no-debug --source=(ods/book) --year=2015

Triggers the import driver to bring in data from the provided `--source`.

`--source=ods`

Optional - either `ods` or `book`. The source the Import Driver should pull from.

`--year=2016`

Optional - four digit year. The starting year to import. If omitted the app will
take the current year and subtract `num_years` to get the starting year to import.



A Symfony project created on March 7, 2017, 11:37 am.
