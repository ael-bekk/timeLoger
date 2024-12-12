#!/bin/bash

DOCKER_COMPOSE="docker-compose -f docker-compose.yml"
CHOSEN_ENV="dev"
REBUILD=""

show_help()
{
    echo "Usage: $0 command env"
    echo "env must be either prod or dev, default: dev"
    echo "Available commands:"
    echo "start                   Start server"
    echo "fixtures                Reset and load fixtures into the database"
    echo "help                    Show this help message"
}

start_server()
{
  echo "Starting server..."
  $DOCKER_COMPOSE up -d $REBUILD
  $DOCKER_COMPOSE exec php rm -rf var/cache
}

load_fixtures()
{
  if [ "$CHOSEN_ENV" == "prod" ]
  then
    CHOSEN_ENV="prod"
    echo -e "\033[31mYou can't load fixtures in production!\033[0m"
    exit 1
  fi
  read -p "This will empty the database, are you sure you want to continue? [Ny]" -n 1 -r
  echo
  if [[ $REPLY =~ ^[Yy]$ ]]
  then
    echo "Loading fixtures into database..."
    $DOCKER_COMPOSE exec --user www-data php composer run empty-db
  fi


}

if [ $# -lt 1 ]
then
    show_help
    exit 1
fi

DOCKER_COMPOSE_OVERRIDE="docker-compose.override.yml"
if [ "$2" == "prod" ]
then
  CHOSEN_ENV="prod"
  echo -e '\033[31mUsing production environment!\033[0m'
  DOCKER_COMPOSE_OVERRIDE="docker-compose.prod.yml"
  REBUILD="--build"
  # Sleep 3 seconds to give chance to cancel
  sleep 3
fi

DOCKER_COMPOSE=$DOCKER_COMPOSE" -f "$DOCKER_COMPOSE_OVERRIDE

case $1 in
    start)
      start_server
      ;;
    help)
      show_help
      ;;
    fixtures)
      load_fixtures
      ;;
    *)
		echo "Invalid command"
      show_help
      exit 1
		;;
esac

