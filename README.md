# Timelogger API
This is the API that [Timelogger CLI](https://gitlab.com/1337FIL/tools/timelogger/timelogger-cli) uses to log sessions.

- [Installation](#installation)
    - [Clone the repository](#clone-the-repository)
    - [Setup SSL](#setup-ssl)
    - [Start the API](#start-the-api)
- [Updating](#updating)


## Installation
This API uses Docker to make it easy to deploy and develop, just make sure that you installed Docker in your VM.

### Clone the repository
You must clone your repository, the recommended path is `/srv/timelogger`
Make sure you download/clone the `main` branch, you can switch to it by running this command in the server:
```shell
git checkout main
```

### Setup SSL
You need to put your SSL certificate in your server in these paths:

```
# This file permissions MUST BE: 644 and owner by root:root
/etc/ssl/${SERVER_NAME}.crt 

# This file permissions MUST BE: 600 and owned by root:root
/etc/ssl/private/${SERVER_NAME}.key
```
If your server is `time-logger.1337.ma` the files will be:
```
/etc/ssl/time-logger.1337.ma.crt 
/etc/ssl/private/time-logger.1337.ma.key
```

### Start the API
To start the server on production simply run:
```shell
# Make sure you you change SERVER_NAME to match your config
SERVER_NAME=time-logger.1337.ma ./server start prod
```

The API will be available on the following ports: *443* and *80*.

### Creating tokens

To create a token to be used with the Timelogger CLI, you can use the console command:
```shell
docker-compose exec --user www-data php ./bin/console app:user:new --help
```

## Updating
If a new release is available just run:
```shell
# Make sure you you change SERVER_NAME to match your config
SERVER_NAME=time-logger.1337.ma ./server update prod
```

