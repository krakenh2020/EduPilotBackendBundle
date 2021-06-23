# KRAKEN Education Pilot - Uni API Backend Bundle

A API for a frontend that provides education verifiable credentials to university students.

- Deployment: https://github.com/krakenh2020/EduPilotDeploymentDocker
- Frontend: https://github.com/krakenh2020/EduPilotFrontend
- API Backend: https://github.com/krakenh2020/EduPilotBackend

Part of the [**H2020 Project KRAKEN**](https://krakenh2020.eu/) and the [**Verifiable Credentials for Student Mobility**](https://api.ltb.io/show/BLUOR) project funded by TU Graz 
as a technologically enhanced administration (TEA) marketplace project.

Based on https://gitlab.tugraz.at/dbp/middleware/dbp-api/api-starter-bundle, a 
Symfony bundle that can be used as a template for creating new bundles for the
DBP API project.


## Development

When including this bundle into your DBP API server it will gain the following
features:

* A custom `./bin/console` command
* An example entity
* Various HTTP methods implemented for that entity


### Integration into the API Server

* Add the repository to your `composer.json`:

```json
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:krakenh2020/EduPilotBackendBundle.git"
        }
    ],
```

* Add the package as a dependency:

```json
    "require": {
        ...
        "kraken/vc4sm-bundle": "dev-master",
        ...
    },
```

* Add the bundle to your `config/bundles.php`:

```php
...
VC4SM\Bundle\Vc4smBundle::class => ['all' => true],
DBP\API\CoreBundle\DbpCoreBundle::class => ['all' => true],
];
```

### Development & Testing

* Install dependencies: `composer install`
* Run tests: `composer test`
* Run linters: `composer run lint`
* Run cs-fixer: `composer run cs-fix`


## License

AGPL-3.0-or-later License, Copyright (c) 2020-2021 Peter Grassberger & KRAKEN consortium

Peter Grassberger <p.grassberger@student.tugraz.at> is [the original](https://github.com/PeterTheOne/vc4sm-backend) Author.
