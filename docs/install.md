# Installation

This package is a private repository. Therefore you **cannot** just install it via *composer ambersive/api*.

## Requirements

- [SSH-Keys](https://barryvanveen.nl/blog/55-installing-a-private-package-with-composer)
- Laravel Project

## Before you start
This package adds a custom user table to your application. Make sure you delete those migration and table files before you start.

## Step 1: Open compose.json and modify the content

The following changes has to be made.

```json
{
    ...
    "require": {
        "php": "^7.4",
        "ambersive/api": "master",
    }
    ...
}
```

This repository works with git-tags e.g. 0.1.0


```json
{
    ...
    "require": {
        "php": "^7.4",
        "ambersive/api": "dev-master#0.1.0",
    },
    ...
}
```

But you can also choose a specific commit

```json
{
    ...
    "require": {
        "php": "^7.4",
        "ambresive/api": "dev-master#6ea3b05",
    },
    ...
}
```

## Step 2: Run composer install

Run the following command. That will install the package and all dependencies.

```bash
composer install
```

## Step 3: Run the api:init command

This package requires some other structures. Therefore run the following command.

```bash
php artisan api:init
```