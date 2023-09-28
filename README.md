# BileMo API

![Symfony](https://img.shields.io/badge/symfony-%23000000.svg?style=for-the-badge&logo=symfony&logoColor=white)
![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)

# Project 7

Building an API to allow businesses to search mobiles from our catalogue.

# Getting started

Fork or clone the repository [here](https://github.com/BenjVA/BileMoAPI)

Assuming you have already everything needed to your local developpement environnement and the symfony framework, replace the settings for the DBMS in ***.env***

Then run 
```bash
composer install
```
This will install all libraries used for this project

Then create the database and update your data fixtures


```bash
php bin/console doctrine:database:create
```
- Generate the database schema :
```bash
php bin/console doctrine:schema:update --force
```
- And run this command to load the initial data fixtures :
```bash
php bin/console doctrine:fixtures:load
```

Launch your symfony server
```bash
symfony server:start
```

You can then use the API to send request with Postman

You can also check for the API documentation at localhost:8000/bilemo/doc

#
Created for the Openclassrooms PHP/Symfony apps developer training.
