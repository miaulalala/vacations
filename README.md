<!--
SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
SPDX-License-Identifier: CC0-1.0
-->

# 🌴 Vacation Requests

Make vacation requests directly from Nextcloud!

Still a **Work in Progress.**

The planned feature set is:

- Send vacation requests directly from NC

- Keep track of bookings for users - List view for all past bookings and their approval status, amount of days taken counter

- Use new "Manager" property to notify managers of vacation requests - fallback email can be set by admins

- Keep track of approving manager via user_id

- Overview for managers of their teams' holidays

- Automated status via Calendar/DAV - dedicated vacation calendar can be defined by admins; VEVENT will contain holiday- taker as ATTENDEE with BUSY status 

- Possiblity for integration with OoO- Sieve integration, etc.

## Building the app

The app can be built by using the provided Makefile by running:

    make

This requires the following things to be present:
* make
* which
* tar: for building the archive
* curl: used if phpunit and composer are not installed to fetch them from the web
* npm: for building and testing everything JS, only required if a package.json is placed inside the **js/** folder

The make command will install or update Composer dependencies if a composer.json is present and also **npm run build** if a package.json is present in the **js/** folder. The npm **build** script should use local paths for build systems and package managers, so people that simply want to build the app won't need to install npm libraries globally, e.g.:

**package.json**:
```json
"scripts": {
    "test": "node node_modules/gulp-cli/bin/gulp.js karma",
    "prebuild": "npm install && node_modules/bower/bin/bower install && node_modules/bower/bin/bower update",
    "build": "node node_modules/gulp-cli/bin/gulp.js"
}
```


## Publish to App Store

First get an account for the [App Store](http://apps.nextcloud.com/) then run:

    make && make appstore

The archive is located in build/artifacts/appstore and can then be uploaded to the App Store.

## Running tests
You can use the provided Makefile to run all tests by using:

    make test

This will run the PHP unit and integration tests and if a package.json is present in the **js/** folder will execute **npm run test**

Of course you can also install [PHPUnit](http://phpunit.de/getting-started.html) and use the configurations directly:

    phpunit -c phpunit.xml

or:

    phpunit -c phpunit.integration.xml

for integration tests
