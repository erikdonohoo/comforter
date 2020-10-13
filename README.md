# Getting Started

I recommend installing (phpbrew)[https://github.com/phpbrew/phpbrew] to help manage different PHP versions
on your machine as this project uses php 7.4. Follow the instructions and make sure you update your
`.bashrc` and then `phpbrew install 7.4 +default`

Make sure you follow the log as it installs and use the `tail` command it gives you.
If you encounter any errors with missing packages, just install them via `brew`.

You will need `docker` and `docker-compose` installed. To setup:

```bash
docker-compose up
```

or if you want it in the background...

```bash
docker-compose up -d
```

Once up, gitlab will be running at `http://localhost:4000`. The default username is `root`, feel free to set any password.
The comforter server itself is running at `http://localhost:8010`. To set up the DB in your DB GUI of choice, just take a look
at the `docker-compose.yml` file to grab the DB info you need.

### Some other helpful docker commands

1. SSH Into a container
```shell
docker ps # get name of container
docker exec -it <name> /bin/bash
```