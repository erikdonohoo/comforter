# Getting Started

I recommend installing (phpbrew)[https://github.com/phpbrew/phpbrew] to help manage different PHP versions
on your machine as this project uses php 7.4. Follow the instructions and make sure you update your
`.bashrc` and then `phpbrew install 7.4 +default +mysql +pdo`

Make sure you follow the log as it installs and use the `tail` command it gives you.
If you encounter any errors with missing packages, just install them via `brew`.

Make sure you are using composer > 2.0

You will need `docker` and `docker-compose` installed. To setup:

```bash
yarn setup
```

Once your DB is up, you can run the following:
```bash
yarn migrate # If this is failing, ssh into the web box and run migrate from there
cd web/
php artisan key:generate --ansi
php artisan passport:keys
```

Once up, gitlab will be running at `http://localhost:4000`. The default username is `root`, feel free to set any password.
The comforter server itself is running at `http://localhost:8010`. To set up the DB in your DB GUI of choice, just take a look
at the `docker-compose.yml` file to grab the DB info you need.

Now setup an app for comforter in gitlab, and add keys to .env

## Development

Once you have the app setup, start the client.

```bash
cd client/
yarn start
```

Because of some issues with the OAuth integration, you have to visit your GitLab over a jump tunnel URL, as
your GitLab needs to be visited both by you in the browser, and have endpoints called by our backend but you can
only register one URL. Since one would work with localhost but the other would need to be a docker URL
JumpTunnel is the only way around this for now.

So in your `.ssh/config` file register port `8010` to one of your available jump tunnel paths and configure that

You can now view the coverage app at `http://localhost:8010/coverage`. You will need to refresh the browser page after
making changes since this app needs to be served by Laravel.
