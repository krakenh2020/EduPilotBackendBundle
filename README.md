vc4sm-bundle
============

a frontend for an API that provides education verifiable credentials to university students.

- Frontend https://github.com/PeterTheOne/vc4sm-frontend
- Backend https://github.com/PeterTheOne/vc4sm-backend

Part of the **Verifiable Credentials for Student Mobility** project funded by TU Graz
as a technologically enhanced administration (TEA) marketplace project.

Based on https://gitlab.tugraz.at/dbp/middleware/dbp-api/api-starter-bundle, a 
Symfony bundle that can be used as a template for creating new bundles for the
DBP API project.

When including this bundle into your DBP API server it will gain the following
features:

* A custom `./bin/console` command
* An example entity
* Various HTTP methods implemented for that entity

Using the Bundle as a Template
------------------------------

* Copy the repo contents
* Adjust the project name in `composer.json`
* Invent a new PHP namespace and adjust it in all PHP files
* Rename `src/DbpStarterBundle` and `DependencyInjection/DbpStarterExtension` to match the new project name

Integration into the API Server
-------------------------------

* Add the repository to your composer.json:

```json
    "repositories": [
        {
            "type": "vcs",
            "url": "git@gitlab.tugraz.at:dbp/middleware/dbp-api/api-starter-bundle.git"
        }
    ],
```

* Add the package to as a dependency:

```json
    "require": {
        ...
        "dbp/api-starter-bundle": "@dev",
        ...
    },
```

* Add the bundle to your `config/bundles.php`:

```php
...
DBP\API\StarterBundle\DbpStarterBundle::class => ['all' => true],
DBP\API\CoreBundle\DbpCoreBundle::class => ['all' => true],
];
```

Development & Testing
---------------------

* Install dependencies: `composer install`
* Run tests: `composer test`
* Run linters: `composer run lint`
* Run cs-fixer: `composer run cs-fix`

license
-------

AGPL-3.0-or-later License, Copyright (c) 2021 Peter Grassberger

Peter Grassberger <p.grassberger@student.tugraz.at> is the Author.

TU Graz has exclusive right of use and the right to grant usage rights and does so as `LGPL-2.1-or-later`,
also see agreement in german below.

> Der/Die Auftragnehmer/in überträgt der TU Graz an den von ihm/ihr erzielten
Arbeitsergebnissen sämtliche wie immer gearteten unbeschränkten,
ausschließlichen und übertragbaren Werknutzungsrechte, welche das Recht
beinhalteten, die Arbeitsergebnisse auf alle dem Urheber/der Urheberin
vorbehaltenen Arten zu benutzen oder benutzen zu lassen. Die TU Graz ist zur
uneingeschränkten Ausübung der Rechte an den Arbeitsergebnissen berechtigt und
hat das Recht, Dritten diese ausschließliche Nutzungsbefugnis zu übertragen oder
diesen ein einfaches Nutzungsrecht einzuräumen. Die Übertragung oben genannter
Rechte ist mit der Bezahlung des vereinbarten Entgelts abgegolten. Ein darüber
hinaus gehendes Entgelt gebührt ausdrücklich nicht.
