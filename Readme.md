# Requirements:
* PHP 7.4+
* Database server: Ver 15.1 Distrib 10.3.21-MariaDB

\* Project uses Symfony 5.1.8

# Project installation

* Clone the repository, enter the directory created
* Install dependencies
```bash
composer install
```
* Configure database connection in .env file
* Create and initialize both main and test databases:
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

# Usage examples

## Login
```bash
curl --location --request POST 'http://127.0.0.1:8000/api/v1/login' \
--header 'Content-Type: application/json' \
--data-raw '{
    "username": "username",
    "password": "password"
}
'

Response:
{"token":"b3zd5a9302c095d97bf3c71b22712ja5"}
```

## Get cells in range
```bash
curl --location --request GET 'http://127.0.0.1:8000/api/v1/sheets/1/cells?left=0&top=0&right=10&bottom=20' \
--header 'X-AUTH-TOKEN: b3zd5a9302c095d97bf3c71b22712ja5'

Response:
{"cells":[{"row":0,"col":0,"value":0},{"row":0,"col":1,"value":1},{"row":0,"col":2,"value":2},{"row":0,"col":3,"value":3},{"row":0,"col":4,"value":4},{"row":0,"col":5,"value":5},{"row":1,"col":0,"value":1},{"row":1,"col":1,"value":2},{"row":1,"col":2,"value":-20},{"row":1,"col":3,"value":4},{"row":1,"col":4,"value":5},{"row":1,"col":5,"value":6},{"row":2,"col":0,"value":2},{"row":2,"col":1,"value":3},{"row":2,"col":2,"value":4},{"row":2,"col":3,"value":5},{"row":2,"col":4,"value":6},{"row":2,"col":5,"value":7},{"row":3,"col":0,"value":3},{"row":3,"col":1,"value":4},{"row":3,"col":2,"value":5},{"row":3,"col":3,"value":6},{"row":3,"col":4,"value":7},{"row":3,"col":5,"value":8},{"row":4,"col":0,"value":4},{"row":4,"col":1,"value":5},{"row":4,"col":2,"value":6},{"row":4,"col":3,"value":7},{"row":4,"col":4,"value":8},{"row":4,"col":5,"value":9}]}
```