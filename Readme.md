# Dabba api

REF: https://oauth2.thephpleague.com/installation/

    openssl genrsa -out private.key 2048
    openssl rsa -in private.key -pubout -out public.key

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
