<p align="center">
    <img src="https://github.com/marblecms/admin/blob/master/docs/logo.png?raw=true" alt="Marble CMS Logo" />
</p>

# Marble CMS 

Marble is a modular, object oriented CMS System for Laravel.

## Installation

### 1. Install Laravel

Install a new Laravel Project.


    composer create-project --prefer-dist laravel/laravel marble "5.4.*"


NOTE: Marble is currently only compatible with Laravel 5.4.

#### Update .env

Update the .env file. If it does not exist already, copy .env.example to .env and fill in your Database credentials et al.

### 2. Add Marble as a composer dependency


    "marblecms/admin": "0.7.*"

And run

    composer update

### 3. Add the Service Provider

Open *config/app.php* and add *Marble\Admin\MarbleServiceProvider::class* to your provider array.

### 4. Update Laravel Configurations

Change the Users Provider in *config/auth.php* to the following:

    'model' => Marble\Admin\App\Models\User::class,

Turn off the MySQL Strict mode in *config/database.php*:

    'strict' => false,

### 5. Final steps

Run the following commands:


    composer dump-autoload

    php artisan vendor:publish --tag=public --force

And last but not least, run the migrations:

    php artisan migrate


After that you should be able to login at *http://your-url/admin/login* with the following credentials:

    User: admin@marble
    Password: admin


## Author

Phillip Dornauer < office@hive.wien >

https://hive.wien


## License

MIT License

Copyright (c) 2017 Phillip Dornauer

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.