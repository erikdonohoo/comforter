#!/bin/bash

set -e

function get_container() {
	NAME="$1"
	REQ="$2"

	if [ "$NAME" == "" ]; then
		if [ "$REQ" == "req" ]; then
			echo "Container name required for this command"
			exit 1
		fi

		CONTAINER=""
		STATUS="none"
	else
		CONTAINER="comforter_${NAME}_1"
		STATUS=`docker inspect --format '{{ .State.Status }}' "$CONTAINER"`
	fi
}

case "$1" in
	help|-h|--help|"")
		docker-compose help
		echo
		echo "Comforter-specific commands:"
		echo "  shell <container>"
		echo "  start|up [container]"
		echo "  stop|down|halt [container]"
		echo "  restart [container]"
		echo "  rm [container]"
		echo "  logs [container]"
		echo "  rebuild [container]"
		echo "  reset-db"
		echo "  prune"
	;;

	shell)
		get_container "$2" req
		echo "Container $CONTAINER is $STATUS"
		if [ "$3" != "" ]; then
			EXEC_USER="$3"
		else
			EXEC_USER="root"
		fi

		# Change tab title to $EXEC_USER@$CONTAINER
		echo -e "\033]0;${EXEC_USER}@${CONTAINER}\007"

		if [ "$STATUS" == "running" ]; then
			docker-compose exec --user "$EXEC_USER" $NAME bash
		else
			docker-compose run --user "$EXEC_USER" --entrypoint /bin/bash $NAME
		fi
	;;

	start|up)
		docker-compose up -d $2
	;;

	stop|down|halt)
		docker-compose stop -t 20 $2
	;;

	restart)
		docker-compose stop -t 20 $2
		docker-compose up -d $2
	;;

	rm)
		docker-compose kill $2
		docker-compose rm -f $2
	;;

	logs)
		docker-compose logs --follow --timestamps --tail 50 $2
	;;

	prune)
		docker system prune
	;;

	pull)
		for DOCKERFILE in docker/*/Dockerfile; do
			IMAGE=`head -n 1 $DOCKERFILE | cut -f 2 -d ' '`
			docker pull "$IMAGE"
		done
		docker-compose pull
	;;

	rebuild)
		docker-compose kill $2
		docker-compose rm -f $2
		docker system prune
		docker-compose build $2
	;;

	reset-db)
		docker-compose stop -t 5 db
		docker-compose rm -f db
		rm -rf "$DIR/db-data/"
		mkdir "$DIR/db-data"
		docker-compose up -d db

		echo -n "Waiting for db to be up."
		while ! (docker-compose exec db mysql -u root -ppassword -e "SELECT email FROM users WHERE user_id=37961" react 2>/dev/null | fgrep root@goreact.com >/dev/null); do
			sleep 1
			echo -n "."
		done
		echo " UP"
		sleep 1
	;;

	*)
		docker-compose "$@"
	;;
esac
