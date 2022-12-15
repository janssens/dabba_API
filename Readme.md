# Dabba api

REF: [https://oauth2.thephpleague.com/installation/](https://oauth2.thephpleague.com/installation/)

    openssl genrsa -out private.key 2048
    chmod 660 private.key
    openssl rsa -in private.key -pubout -out public.key
    chmod 660 public.key

    php -r 'echo base64_encode(random_bytes(32)), PHP_EOL;'

    webpack encore build

    symfony run -d yarn encore prod

## Running the project locally using Docker

Install Docker, and run the command below

```
docker compose up
```

You will need to define the `DATABASE_URL` variable like below (using a `.env.local` file, for example)

```
DATABASE_URL="mysql://dabba:dabba@mariadb:3306/dabba?serverVersion=10.3"
```

When running the project for the first time, you will need to create the database schema.
You can also create a user to access the admin area.

```
docker-compose exec php bin/console doctrine:schema:create
docker-compose exec php bin/console app:user:create --super-admin
```

composer install
```
docker-compose exec php composer install
```

yarn install & watch (webpack)
```
docker-compose exec php yarn
docker-compose exec php yarn watch
```
```
docker-compose exec php bin/console assets:install
```

clean cache
```
docker-compose exec php bin/console cache:clear
```

mysql
```
docker-compose exec mariadb mysql -u dabba -pdabba dabba -e "SHOW TABLES;"
```

```
docker-compose exec php chown www-data:www-data public.key private.key
```
