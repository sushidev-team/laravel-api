# Schema files
This package is using yaml files to declare the required actions etc. 

The following example shows the maximum size of a schema yaml file. Below that you will find an explanation of the schema file.

```
table: roles
model: App\Models\Security\Role
resource: App\Http\Resources\Security\RoleResource
collection: App\Http\Resources\Security\RoleCollection
policy: App\Policies\Security\RolePolicy
locked: false
lockedHard: false
fields:
    id:
        type: uuid
        description: ''
        example: 73fa78f6-c0d5-484a-92db-d63392e1a82b
        required_create: false
        required_update: false
        encrypt: false
        hidden: false
    name:
        type: string
        description: ''
        example: ''
        required_create: false
        required_update: false
        encrypt: false
        hidden: false
    guard_name:
        type: string
        description: ''
        example: ''
        required_create: false
        required_update: false
        encrypt: false
        hidden: false
    created_at:
        type: date
        description: ''
        example: '2019-08-01 00:00:00'
        required_create: false
        required_update: false
        encrypt: false
        hidden: false
    updated_at:
        type: date
        description: ''
        example: '2019-08-01 00:00:00'
        required_create: false
        required_update: false
        encrypt: false
        hidden: false
imports:
    - Auth
    - DB
traits: {  }
implement: {  }
extends: BaseModel
appends: {  }
casts: {  }
relations: null
methods: {  }
roles:
    Role:
        description: 'This role will have access to all endpoints of the table:roles'
        permissions:
            - '*'
permissions:
    - roles-all
    - roles-index
    - roles-show
    - roles-update
    - roles-store
    - roles-destroy
endpoints:
    all:
        name: all
        include: true
        model: null
        resource: null
        fields:
            - '*'
        permissions:
            - roles-all
        policy: all
        where: {  }
        with: {  }
        order: {  }
        hookPre: {  }
        hookPost: {  }
        validations: {  }
        middleware:
            - 'auth:api'
        summary: ''
        description: ''
        route: ''
        tags: {  }
        requestParams: {  }
    index:
        name: index
        include: true
        model: null
        resource: null
        fields:
            - '*'
        permissions:
            - roles-index
        policy: all
        where: {  }
        with: {  }
        order: {  }
        hookPre: {  }
        hookPost: {  }
        validations: {  }
        middleware:
            - 'auth:api'
        summary: ''
        description: ''
        route: ''
        tags: {  }
        requestParams: {  }
    show:
        name: show
        include: true
        model: null
        resource: null
        fields:
            - '*'
        permissions:
            - roles-show
        policy: view
        where: {  }
        with: {  }
        order: {  }
        hookPre: {  }
        hookPost: {  }
        validations: {  }
        middleware:
            - 'auth:api'
        summary: ''
        description: ''
        route: ''
        tags: {  }
        requestParams:
            - id
    update:
        name: update
        include: true
        model: null
        resource: null
        fields:
            - '*'
        permissions:
            - roles-update
        policy: update
        where: {  }
        with: {  }
        order: {  }
        hookPre: {  }
        hookPost: {  }
        validations: {  }
        middleware:
            - 'auth:api'
        summary: ''
        description: ''
        route: ''
        tags: {  }
        requestParams:
            - id
    store:
        name: store
        include: true
        model: null
        resource: null
        fields:
            - '*'
        permissions:
            - roles-store
        policy: store
        where: {  }
        with: {  }
        order: {  }
        hookPre: {  }
        hookPost: {  }
        validations: {  }
        middleware:
            - 'auth:api'
        summary: ''
        description: ''
        route: ''
        tags: {  }
        requestParams: {  }
    destroy:
        name: destroy
        include: true
        model: null
        resource: null
        fields:
            - '*'
        permissions:
            - roles-destroy
        policy: destroy
        where: {  }
        with: {  }
        order: {  }
        hookPre: {  }
        hookPost: {  }
        validations: {  }
        middleware:
            - 'auth:api'
        summary: ''
        description: ''
        route: ''
        tags: {  }
        requestParams:
            - id
endpoints_exclude: {  }
schemaResource:
    id:
    test:
        type: 'string'
        example: 'test'
        description: 'lorem ipsum'
```

## General

The important part to understand if you look at this schema file is the goal to include any important setting except the database.

This schema file will be generated based on the database. Means it requires a valid processed migration. 

## Structure

The base structure of every schema file looks like this.

```yml
table: TABLENAME
model: CLASSNAME OF MODEL incl. namespace
resource: CLASSNAME OF RESOURCE incl. namespace
collection: CLASSNAME OF COLLECTION incl. namespace
policy: CLASSNAME OF POLICY incl. namespace
locked: false
fields:
    ...
imports:
    - ...
implement: {  }
extends: BaseModel
appends: {  }
casts: {  }
relations:
    ...
roles:
    ...
permissions:
    - PERMISSIONS..
endpoints:
    - ENDPOINTS...
endpoints_exclude: {  }

```

### Basic

The important parts of the schema are at the beginning. 

- Tablename
- Model
- Resource
- Collection
- Policy

All this entries are classnames with the full namespace.
eg. App\Models\Users\User for the User model.

### Locked

This attribute defines if the data of the related files (Controller, Model etc.) will be changed if the update commands got triggered. The only thing which will be changed is the \<Locked\> attribute within the seperat files.

### Fields

Usally the api creation tool tries to create a field definition list based on the database structure. If the extraction works without any exception the default look like this:

```yml
...
fields:
    id:
        type: uuid
        description: ''
        example: 06a1ae68-94c9-4a23-88ad-dd8c1d67ab00
        required_create: false
        required_update: false
        encrypt: false
        hidden: false
    filename:
        type: string
        description: ''
        example: ''
        required_create: false
        required_update: false
        encrypt: false
        hidden: false
...
```

Every entry has a set of possible options:

- **type**: Define if a value needs to be casted. Also important for documentation
- **description**: Important for the documentation. Should descripe the content of the column. This field is empty by default.
- **example**: Provide an example entry for the documentation
- **required_create**: Defines if this field should be required for the create route (POST)
- **required_update**: Defines if this field should be required for the update route (PUT) 
- **encrypt**: Defines that a field should be encrypted. Please be aware that the minimum requirement for that is a longText field, otherwise decryption will fail.
- **hidden**: Defines if a field should be hidden 

### Import / Implements

Sometimes you need to import classes into your model etc. or implement or extend a class. Therefore you will need to make use of the following block:

```yml
...
imports:
    - Auth
    - DB
    - App\Helpers\Test1 as TEST
implement: 
    - TEST
extends: BaseModel
...
```

- **imports**: Define a list of classes which should be imported in every file.
- **extends**: This part has only effect in the model. It will replace or add a class extentsion to the given model class.
- **imporment**: Add a list of implementation classes to a model.

## Other model related schema declarations

```yml
appends: 
    - field1
    - field2
casts: 
    field1: boolean 
```

Normally the **casts** will be automatically created based on the field type. If you wish to add seperat casts definitions or overwrite an existing one, you can use the cast section of the schema file.


The **appends** section accepts a list of fields you might want to add.

Please be aware that the related *getFieldAttribute* function must be added by your self. This should be done in the *custom:methods* area.

### Model relations

If you want to add model relations you can add them in the relations section:

```yml
relations:
    users:
        name: users
        field: user_id
        field_foreign: id
        type: belongsTo
        model: null
        with: {  } // If you want add a ->with() statement to the relation. This attribute accepts a list.
        order:
            created_at: DESC
```


### Security / Permissions 

Every appliation has the need to provide a set of permissions to secure the content or function of the application. Therefore this schema also provides a handy helper to make use of [spatie/laravel-permission](https://github.com/spatie/laravel-permission) package.

```yml
roles:
    Users_avatar:
        description: 'This role will have access to all endpoints of the table:users_avatars'
        permissions:
            - '*'
permissions:
    - users-avatars-all
    - users-avatars-index
    - users-avatars-show
    - users-avatars-update
    - users-avatars-store
    - users-avatars-destroy
```

By default this section will be filled with the CRUD permissions for the table.

Also a role be added.

Please beware that every role and permission within this list will be seeded to the applicaiton if you run "php artisan api:seed".

Additional to that the application also implements the standard policy logic, which will return true by default. You will need to customize those policy entries by yourself. You can find further information the section "[Define your Policy](tutorial_policy.md)".

## Endpoints

One of the main goals of this package is to speed up the development of standard endpoints and unify the response. Therefore this schema offers the possibilty to define endpoints.

It accepts objects.

```yml
endpoints:
    all:
        name: all
        include: true
        model: null
        resource: null
        fields:
            - '*'
        permissions:
            - users_avatars-all
        policy: all
        where: {  }
        with: {  }
        order: {  }
        hookPre: {  }
        hookPost: {  }
        validations: {  }
        middleware:
            - 'auth:api'
        summary: ''
        description: ''
```

In this case the endpoint's name is all.
Default there are also: index, show, update, store, destroy

Options available:

- **name**: Define the name of the endpoint.
- **include**: Should be added to the controller. If this setting is false the controller function will not be added to the application.
- **model**: Define which model should be passed to the controller.
- **resource**: Define which resource should be passed to the controller.
- **fields**: Define a list of fields which should be requested on ->load(). This will be like you do a ->select() statement in eloquent.
- **permissions**: List of permissions which are required. The permission check will normally done before every database stuff is done.
- **policy**: Define the policy method which should be called. Accepted values are *null* and any kind of string.
- **where**: List of methods that should be called. Be aware that you need to use the prefix where + first letter uppercase in the actual method name. eg. search => whereSearch
- **with**: Add relations to the model if you go to the database.
- **order**: Object of order information:

```yml
...
order: 
    firstname: ASC
    lastname: DESC
...
```   

- **hooksPre**: Define a list of methods that should be called before you do database interaction. Normally this is the place where you do custom validation stuff or database requests.

- **hooksPost**: Define a list of methods that should be called after you have done the standard database interaction. Normally this is the place where you do relation stuff.

- **validations**: Define a object custom validation rules which will be merged with the automate created.

- **middleware**: List of of middleware that should be called before you anything in the controller method.

- **summary** & **description**: Define a information to the endpoint so the automated documentation creation will have some further information about this endpoint.

## Documentation

The schema allows you to customize the documentation for endpoints.  The following minified/reduced version of the relevant parts of the shows you the possiblities:

```yml
    show:
        summary: ''
        description: ''
        route: ''
        tags: {  }
        requestParams:
            - id
```

- **route**: Provide the url for your endpoint. eg. /api/roles
- **tags**: Array of tags
- **requestParams**: eg. the id in the url or a query string param

###requestParams
Allowed Values: String or Array
If a string is passed the SchemaHelper will look if there is a corresponding field definition within this schema. If so it will take it.

If an array is passed it will do the same but also a merge with your passed values.

That means you can define something like:

```yml
    show:
        summary: ''
        description: ''
        route: ''
        tags: {  }
        requestParams:
            - id:
                in: 'path'
                type: integer
                example: 1
                description: 'This is an integer instead of a string'
```

### Other documentation areas in the schema file

#### Resource

The resource file might differ from model response.
Therefor the schema file provides the possibliy to define the documentation for the resourcefile from within schema file.

If the key exists in the field list it will be taken from there. But you can overwrite it by defining the properties.

```yml
schemaResource:
    id:
    test:
        type: 'string'
        example: 'test'
        description: 'lorem ipsum'
```