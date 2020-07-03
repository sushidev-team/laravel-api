# Users

Per default the *users.yml* schema file will be set to **lockedHard=true**.

This means that neither a controller or policy or any other file will be created. In fact that is really not required cause the package has already an implementation for the user controller.

If you need to modify the user schema you will need to modify the schema file from:

```yaml
table: users
model: AMBERSIVE\Api\Models\User
resource: AMBERSIVE\Api\Resources\UserResource
collection: AMBERSIVE\Api\Resources\UserCollection
policy: AMBERSIVE\Api\Policies\Users\UserPolicy
locked: true
lockedHard: true
```

to 

```yaml
model: App\Models\Users\User
resource: App\Http\Resources\Users\UserResource
collection: App\Http\Resources\Users\UserCollection
policy: App\Policies\Users\Users\UserPoliy
locked: false
lockedHard: false
```

Otherwise the the folder structure might be weirrd and "broken" due to the fact that the namespace will be automatically created from the model declaration.