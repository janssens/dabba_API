# Dabba api

REF: https://oauth2.thephpleague.com/installation/

    openssl genrsa -out private.key 2048
    openssl rsa -in private.key -pubout -out public.key

    php -r 'echo base64_encode(random_bytes(32)), PHP_EOL;'

    webpack encore build

    symfony run -d yarn encore prod