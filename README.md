# Building a Laravel API with Laravel Sanctum SPA

In this tutorial I'll go through every details needed to configure your API using Laravel Sanctum SPA Authorization.

For a detailed explanation about Laravel Sanctum head [here](https://laravel.com/docs/10.x/sanctum#spa-authentication).

## Quick notes

SPA authentication requires that your API and your SPA share the same top-level domain. For example:

* http://domain.com and http://api.domain.com is a valid setup
* http://spa.com and http://api.com isn't a valid setup

If your intent is developing an API that will be used by different domains you should head to [Laravel Sanctum API Token Authentication](https://laravel.com/docs/10.x/sanctum#api-token-authentication)

## First steps

First things first: you need to create the base laravel project using the command

```bash
composer create-project laravel/laravel <YOUR_PROJECT_NAME>
```

After that you should run you migrations with <code>php artisan migrate:fresh --seed</code> to populate your database. Don't forget to configure your database credentials inside your *.env* file. It should look something like this:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=<YOUR_DATABASE_NAME>
DB_USERNAME=<YOUR_USERNAME>
DB_PASSWORD=<YOUR_PASSWORD>
```

After creating the project you can run it using artisan's built-in localhost server with

```bash
php artisan serve
```

With these steps done, you'll have configured the base of your Laravel project as you would normally and you can access it by going to your project's home page in http://localhost:8000 or http://127.0.0.1:8000

## Authentication

There are several ways to develop an authentication process in Laravel, but I'll use a scaffolding named [Laravel Breeze](https://laravel.com/docs/10.x/starter-kits#breeze-and-inertia) to speed things up. You can develop your own authentication process if you want to, but you will need to perform the analog configurations to your API.

To use Laravel Breeze you will first need to install it via composer and then initialize it in your application by using the following commands

```bash
composer require laravel/breeze
php artisan breeze:install api
```

<small><em>The <code>api</code> argument tells Laravel Breeze to scaffold an API-like application. This means that several entities like views and other frontend-related Javascript and CSS files will be removed from the project</em></small>

After these steps, your project is almost fully configured as an API but you'll need to do some minor tweaks.

## CORS

When dealing with APIs you'll almost always find yourself receiving requests from domains that aren't your own. For example, you may develop an API to translate an specific list of words and you want others to use it. In this case you might receive requests from "https://domain1.com" or "http://domain2.com". In both cases these requests come from domains that probably differ from yours (in this case "localhost"). By default, browsers block these requests unless your API define a series of [CORS](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS) HTTP-based headers.

With Laravel Sanctum all this header configuration is abstracted and wrapped inside the *cors.php* configuration file in *config/cors.php*. When you open it you'll find an array with some predefined keys and values. Some keys translate to an analog HTTP header, i.e, *allowed_origins* represents the *Access-Control-Allow-Origin* header responsible for telling the browsers which domains can make requests to your API. In most cases you will surely want everyone to do so and, in such cases, you should use the "*" wildcard. However this is not the case when using Laravel Sanctum SPA authorization.

If you followed the steps thoroughly the *allowed_origins* key should look something like this:


```php
'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000')]
```

If you SPA isn't using port 3000, you should go to your *.env* file and configure the FRONTEND_URL variable with the correct port as in the following example:

```ini
FRONTEND_URL=http://localhost:5173
```

## Maintaining State

After configuring the CORS headers of your application you might try to log in by making a POST request to http://localhost:8000/login from your SPA and will probably receive a 401 HTTP error code from your requests. If you followed the steps from Laravel Sanctum documentation and [retrieved the XSRF-TOKEN](https://laravel.com/docs/10.x/sanctum#csrf-protection) this means that the SPA isn't actually sharing the session with the API. This is happening because your SPA domain didn't pass Laravel Sanctum's check that validates if the requesting origin should share the session cookie. The validation happens inside <code>\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::fromFrontEnd</code> and basically tests if the referer/origin matches any of the hosts defined inside the *sanctum.stateful* configuration defined in the *sanctum.php* configuration file. To solve it you can either change *sanctum.stateful* directly or simply define a SANCTUM_STATEFUL_DOMAINS variable inside your .env file. This variable must **NOT** contain a trailing slash neither the schema (http://, https://). If your SPA domain is, for example, "https://frontendapp.com:3000" the variable should be set as "frontendapp.com:3000". In my case I'll set it to "localhost:5173"

Another thing you should do is set your SESSION_DRIVER and SESSION_DOMAIN environment variables to "cookie" and ".&lt;YOUR_TOP_LEVEL_DOMAIN&gt;". It should look something like this:

```ini
SESSION_DRIVER=cookie
SESSION_LIFETIME=120
SESSION_DOMAIN=.localhost
```

<small><em>Prepending a "." to your top-level domain you tell Laravel that the session cookie should be shared with any subdomain from you top-level domain</em></small>

### Cookie persistence

If you retry loggin in, you might now encounter another problem where the API redirects you to http://localhost:8000/home. This is happening because, although you didn't share the session cookie, it was set in your browser. You can check it inside the Application Tab in you browsers Dev Tools. After clearing the cookies you should try loggin in one more time.

## Wrapping up

Now you are able to finally log in using Laravel Sanctum SPA Authorization and take advantage of Laravel's built-in CSRF protection and session handling as if you were using Laravel as a fullstack solution. Enjoy it!
