# Useage of the api

## Step 1: Create a migration

This package and all the schemafiles are based on the database declaration. This means that the schema file can only be created if the table is already migrated.

Therefore it is required to create the migration + run the migration.

### Create the migration
```bash
php artisan make:migration create_TABLENAME_table
```

Define your columns and save the file.

### Run the migrations
```bash
php artisan migrate
```

## Step 2: Run the api command

```bash
php artisan api:make --table=users --model=Users/User
```
Please be aware that the models are stored in the models folder. This folder can be changed by changing the config key.

## Step 3: Modify the schema file

Modify the schema file. This file is located in the Schemas folder within your app folder.

## Step 4: Run the api:update command

```bash
php artisan api:update
```

This command will generate and update all controller and models etc.
Please be aware that this command will also trigger a clean-up routine with automatic code alignment.

## Step 5: Add the route to your api.php 

```php
Route::api('users', '\App\Http\Controllers\Api\Controller\Users\UserController', [], 'users', []);
```

This will automatically create the following api endpoints to the application.

- [GET] /api/users/all
- [GET] /api/users
- [GET] /api/users/:id
- [POST] /api/users
- [PUT] /api/users/:id
- [DELETE] /api/users/:id

---

## Attention:

The schema file also contains the permissions and roles for the given endpoints. By running the following command the permissions will be synced.

```bash
php artisan db:seed
```