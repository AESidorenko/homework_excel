# Requirements:
* PHP 7.4+
* Database server: Ver 15.1 Distrib 10.3.21-MariaDB

\* Project powered by Symfony 5.1.8

# Project installation

* Clone the repository, enter the directory created
* Install dependencies
```bash
composer install
```
* Configure database connection in .env file
* Create and initialize main and test databases:
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test
```
* Load data fixtures:
```bash
php bin/console doctrine:fixtures:load
```
  _now you can use a demo user credentials: username: "user1", password: "password"_ 
* Start the built-in Symfony server:
```bash
symfony server:start -d
``` 
Use the following base URI to access the API methods:
```
http://127.0.0.1:8000
```

