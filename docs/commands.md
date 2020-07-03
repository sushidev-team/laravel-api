# List of all available commands

## api:init

This package is using prettier for file formatting.
Therefore the requirements for the application must be installed.
This command will update the package.json file and will run the installation of prettier.

## api:prepare

Will create the folders for the schema files and also for the models based on the seeting made in the ambersive-api.php config file.

## api:new

This command will automatically create a migration file based on the following structure:

[Y_m_d_His]_create_*TABLENAME*_table.php or alter_*TABLENAME*_*[His]* (if the table already exists on the database)

The command tries to detect automatically if it is a create or alter table file.

## api:make

Params:
--table=NAME-OF-TABLE (e.g. users)
--model=Path and name of Model (eg Users/User)

This command will look into the database and will create a base schema file based on the settings there.

## api:update

Update all files based on the schema file definitions.
This command will not be changing anything if the attribute of the schema file is locked.