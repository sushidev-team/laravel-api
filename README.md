# Laravel API Helper Package
by AMBERSIVE KG / Manuel Pirker-Ihl (manuel.pirker-ihl@ambersive.com / @leganz on Twitter)

Status: Active Development

## About

The main goal of this project is to provide a tested environment for a fast api endpoint creation. 
The project will automatically create the required php files and ensure that also a minimum quality standard is provided.

This packages will help you create:

- Models (+ documentation)
- Resources (+ documentation)
- Collections (+ documentation)
- Controller (+ documentation)
- Tests (Models/Controller)
- Policy
- Factory 

based on some simple yaml schema files.

This packages also comes with some out-of-the-box endpoint implemmentations (restful endpoints) like

- login
- registration
- lost password
- permissions (CRUD)
- roles (CRUD)

Changes of each version can be read in [CHANGELOG.md](CHANGELOG.md).

## Supported databases

Currenty supported databases are

- MySQL 5.x
- Sqlite

Please be aware that some functionality will not be there due to restrictions in the database technology (e.g. relations resolving).

## Used packages 
- [pragmarx/yaml](https://github.com/antonioribeiro/yaml)
- [emadadly/laravel-uuid](https://github.com/EmadAdly/laravel-uuid)
- [darkaonline/l5-swagger](https://github.com/DarkaOnLine/L5-Swagger)
- [spatie/laravel-permission](https://github.com/spatie/laravel-permission)
- [tymon/jwt-auth](https://github.com/tymondesigns/jwt-auth)

## Other requirements

This package is using prettier to automatically format files via command line.
NodeJs is a must have requirement.

## Installation

#### 1) Run the install command

```bash
composer require ambersive/api
```


#### 2) Installation of all nodejs requirements

Before you can process please delete all default migration files. (eg. for users table). Please notice that the following command will move some files from your config folder into a "ori" folder as backup, cause this package will overwrite some setting in the basic auth.php config file.

```
php artisan api:init
```

Then migrate the databse to be sure all the required tables are migrated.
```
php artisan migrate
```

## Default setup

This package provides some standard implementations of the 
- login
- registration
- forgot password / set password
- user endpoints (incl. current user)

and some basic endpoints for permissions, roles and users. Some of the basic endpoints can be replaced and customized. For further information please read the documentation.

#### Seeds

This package also includes some seeds incl. user seeding. During the setup process seed files will be created in *resources/seedfiles*.

#### Creating folders and files

Schemafiles and most of the required files are stored in specfic folders. Via the ambersive-api config file all of those paths can be set. The following command will create the required folders. You will not need to run this command if you run the api:init command.

```
php artisan api:prepare
```

## Documentation

Further information about this package can be found [here](docs/overview.md).

