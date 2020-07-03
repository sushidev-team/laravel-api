# Registration

Currently there are two workflows implemented.

- Registration with activation code (Default)
- Automatic Account activation

The related config key is:

````
ambersive-api.automatic_active
````
The config can also be adressed by using the environement variable *API_ACTIVATION_AUTO* (boolean)

## Registration with activation code

In this case the account will remain as not activated unless the activation url is called. The code and the complete url will be passed to the Mailable and be sent to the user.

Example for the activation url (endpoint):
````
[GET] http://localhost/api/auth/activation/{code}
````

The default mail:

```html
<html>
    <head>
        <title></title>
    </head>
    <body>
        <div class="container">
                <p>Hello Test!</p>
                <p>Your activation code is &quot;b1OhmMrSkvPw4OK4pK91hrEgLIPeadPmajXaRoKB&quot;.  
                    <a href="http://localhost/api/auth/activation/b1OhmMrSkvPw4OK4pK91hrEgLIPeadPmajXaRoKB" target="_blank">
                        Click here to activate your account.
                    </a>
                </p>
        </div>
    </body>
</html>  
```

Both the default base layout and the mail layout for the activation mail can be overwritten by using the configs.

#### Mail configuration

The base layout config key is situated in the mail.php file with the name "*layout*".

The activation mail view is stored in the picaipe-mails.php config file.
Change the value to a preferred other view.

Please be aware that only following attributes are available to the activation mail:

- User model ($user)
- Activation code ($code)
- Activation url ($url)

## Registration with automatic activation

If this setting is true no activation mail will be sent and the email address will be marked as verified. Please be aware that this might cause legal troubles if not used correct.